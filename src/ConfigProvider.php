<?php
declare(strict_types = 1);

namespace ExpressivePrismic;
use Prismic;
use Zend\Expressive\Application;

use Zend\Expressive\Middleware\NotFoundHandler;
use Zend\Expressive\Middleware\ErrorResponseGenerator;

/**
 * Class ConfigProvider
 *
 * @package ExpressivePrismic
 */
class ConfigProvider
{

    /**
     * Return configuration
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'view_helpers' => $this->getViewHelperConfig(),
            'templates'    => $this->getTemplateConfig(),
            'prismic'      => $this->getPrismicConfig(),
        ];
    }

    /**
     * Container Config
     * @return array
     */
    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                // Api Instance
                Prismic\Api::class => Factory\ApiFactory::class,

                // Default Link Resolver
                LinkResolver::class => Factory\LinkResolverFactory::class,

                // Helper class that helps match the configured routes with properties of prismic documents
                RouteMatcher::class => Factory\RouteMatcherFactory::class,

                // Mapping Route Parameters
                Service\RouteParams::class => Service\Factory\RouteParamsFactory::class,

                // Middleware

                // For rendering document based templates
                Middleware\PrismicTemplate::class => Middleware\Factory\PrismicTemplateFactory::class,

                // Sets the matched document as a request attribute
                Middleware\DocumentResolver::class => Middleware\Factory\DocumentResolverFactory::class,

                // A Midleware Pipeline containing the cache busting middleware that you can manipulate with a delegator factory
                'ExpressivePrismic\Middleware\WebhookMiddlewarePipe' => Middleware\Factory\WebhookMiddlewarePipeFactory::class,

                // Processes Webhooks from Prismic.io and busts the cache
                Middleware\ApiCacheBust::class => Middleware\Factory\ApiCacheBustFactory::class,

                // Processes a preview token and redirects to the page being previewed
                Middleware\PreviewInitiator::class => Middleware\Factory\PreviewInitiatorFactory::class,

                // Injects javascript for running A/B tests with Prismic and Google Analytics
                Middleware\ExperimentInitiator::class => Middleware\Factory\ExperimentInitiatorFactory::class,

                // Injects javascript to display the preview toolbar
                Middleware\InjectPreviewScript::class => Middleware\Factory\InjectPreviewScriptFactory::class,

                // Middleware that sets Request Attributes with the bookmarked 404 document when a 404 is in process
                Middleware\NotFoundSetup::class => Middleware\Factory\NotFoundSetupFactory::class,

                // The Pipeline that runs as the outermost middleware for rendering 404 errors
                'ExpressivePrismic\Middleware\NotFoundPipe' => Middleware\Factory\NotFoundPipeFactory::class,

                // Custom error response generator intended to replace the default in Zend Expressive
                Middleware\ErrorResponseGenerator::class => Middleware\Factory\ErrorResponseGeneratorFactory::class,
                // The Pipeline that runs when an error (exception) occurs
                'ExpressivePrismic\Middleware\ErrorHandlerPipe' => Middleware\Factory\ErrorHandlerPipeFactory::class,
            ],
            'invokables' => [
                // An instance used to track the current document for the request
                Service\CurrentDocument::class         => Service\CurrentDocument::class,

                // Turns 404 errors into exceptions
                Middleware\NormalizeNotFound::class    => Middleware\NormalizeNotFound::class,
            ],
            'aliases' => [
                /**
                 * Alias to the Prismic\LinkResolver namespace
                 * 'Prismic\LinkResolver' is used throughout the codebase so
                 * that consumers can replace the default link resolver
                 * with their own implementation and retain the use of everything else
                 */
                Prismic\LinkResolver::class            => LinkResolver::class,

                /**
                 * Replace the shipped NotFoundHandler with a custom pipeline to render 404 errors from the CMS
                 */
                NotFoundHandler::class => 'ExpressivePrismic\Middleware\NotFoundPipe',

                /**
                 * Replace the shipped ErrorResponseGenerator with our own in order to render 500 errors from the CMS
                 */
                ErrorResponseGenerator::class => Middleware\ErrorResponseGenerator::class,
            ],
            'delegators' => [
                Application::class => [
                    // Sets up routing for webhooks and previews
                    Factory\PipelineAndRoutesDelegator::class,
                ],
            ],
        ];
    }

    /**
     * View Helper Config
     * @return array
     */
    public function getViewHelperConfig() : array
    {
        return [
            'factories' => [
                // Turns Prismic.io Documents and Links into local URLs in the view
                View\Helper\Url::class      => View\Helper\Factory\UrlFactory::class,
                // View helper makes it easier to retrieve fragment values for the current document in the view
                View\Helper\Fragment::class => View\Helper\Factory\FragmentFactory::class,
            ],
            'aliases' => [
                'prismicUrl' => View\Helper\Url::class,
                'fragment'   => View\Helper\Fragment::class,
            ],
        ];
    }

    /**
     * Return template configuration
     * @return array
     */
    public function getTemplateConfig() : array
    {
        return [
            'map' => [
                'layout::error-fallback'  => __DIR__ . '/../templates/fallback-error-layout.phtml',
                'error::prismic-fallback' => __DIR__ . '/../templates/fallback-error.phtml',
            ],
        ];
    }

    /**
     * Return Prismic specific config
     * @return array
     */
    public function getPrismicConfig() : array
    {
        return [
            // Api Connection Params
            'api' => [
                // Permanent Access Token
                'token' => null,
                // Api Endpoint
                'url' => null,
                // Api Cache TTL in seconds. Set to 0 to cache forever (recommended)
                'ttl' => null,
            ],

            // Webhook Shared Secret
            'webhook_secret' => null,

            /**
             * Error Handler configuration for content managed
             * error pages in production
             */
            'error_handler' => [

                /**
                 * A Pipeline is constructed that attempts to render a 404 document
                 * stored in the prismic API. The default config replaces the 404
                 * handler provided by Zend with this pipline, so it's theoretically
                 * zero config, except we need to know which bookmark to get from the api
                 * and the template used to render the doc.
                 *
                 * You can also modify the pipeline to inject your own middleware,
                 * either by adding elements to the middleware array, or by using a
                 * delegator factory.
                 */
                'bookmark_404'      => null,
                'template_404'      => 'error::404',
                'middleware_404'    => null,
                /**
                 * Default Pipe
                 *
                 * [
                 *     Middleware\InjectPreviewScript::class,
                 *     Middleware\ExperimentInitiator::class,
                 *     Middleware\NotFoundSetup::class,
                 *     Middleware\PrismicTemplate::class,
                 * ],
                 */

                /**
                 * If the error document cannot be loaded, you have the choice to have an exception
                 * thrown, or to continue with the normal 404 rendering process available in Expressive
                 */
                'render_404_fallback' => false, // false = throw exceptions

                /**
                 * The error/exception handler works in the same way as 404's
                 * We need a bookmark and a template to render
                 */
                'bookmark_error'    => null,
                'template_error'    => 'error::error',

                /**
                 * The pipe line can be overridden by providing an array of middleware here,
                 * the default is shown:
                 *
                 * 'middleware_error' => [
                 *     Middleware\InjectPreviewScript::class,
                 *     Middleware\ExperimentInitiator::class,
                 *     Middleware\PrismicTemplate::class,
                 * ],
                 */
            ],

            /**
             * URL of the Prismic toolbar Javascript
             * This JS File renders the toolbar when in preview mode and is also
             * responsible for setting experiment cookies too
             */
            'toolbarScript' => '//static.cdn.prismic.io/prismic.min.js',

            /**
             * This 'endpoint script' is injected by the experiment initiator or the
             * preview initiator, or both and it let's the main Prismic.io JS file know
             * which endpoint to use. As there's a chance it may end up in the source twice,
             * check for the global prismic object before initialising it.
             *
             * It's in printf format and there should be 1 variable interpolated which is
             * the url of the repository's endpoint, i.e. $config['prismic']['api']['url']
             */
            'endpointScript' => 'var prismic = window.prismic || {};  prismic.endpoint = \'%s\';',

            /**
             * Determines the parameter names we look for inroutes to identify
             * a document.
             * Set these in local config to override the defaults:
             * @see Service\RouteParams
             */
            'route_params' => [
                //'id'       => 'prismic-bookmark',
                //'bookmark' => 'prismic-id',
                //'uid'      => 'prismic-uid',
                //'type'     => 'prismic-type',
            ],
        ];
    }

}
