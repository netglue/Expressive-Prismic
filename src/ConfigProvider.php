<?php
declare(strict_types = 1);

namespace ExpressivePrismic;

use Prismic;
use Zend\Expressive\Application;

use Zend\Expressive\Handler\NotFoundHandler;
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
                /**
                 * Because the Api is no longer usable without a link resolver, the alias 'ExpressivePrismic\ApiClient'
                 * returns the Api Client whereas the alias Prismic\Api retrieves the Api Client and setter injects the
                 * Link Resolver. This leaves a single point to retrieve the Api from, for BC but ensures the link
                 * resolver is also injected. The link resolver can still be overridden by aliasing Prismic\LinkResolver
                 * to something else, but if you're doing that, you're probably aware of the cyclic dependency anyway.
                 */

                // Api Instance, Configured with Link Resolver
                Prismic\Api::class => Container\ApiFactory::class,

                // Api Instance without a Link Resolver configured
                'ExpressivePrismic\ApiClient' => Container\ApiClientFactory::class,

                // Default Link Resolver
                LinkResolver::class => Container\LinkResolverFactory::class,

                // Helper class that helps match the configured routes with properties of prismic documents
                RouteMatcher::class => Container\RouteMatcherFactory::class,

                // Mapping Route Parameters
                Service\RouteParams::class => Container\Service\RouteParamsFactory::class,

                // Middleware

                // For rendering document based templates
                Middleware\PrismicTemplate::class => Container\Middleware\PrismicTemplateFactory::class,

                // Sets the matched document as a request attribute
                Middleware\DocumentResolver::class => Container\Middleware\DocumentResolverFactory::class,

                // A Middleware Pipeline containing the cache busting middleware
                // that you can replace with a custom pipeline if required
                Middleware\WebhookPipe::class
                    => Container\Middleware\WebhookMiddlewarePipeFactory::class,

                // Processes Webhooks from Prismic.io and busts the cache
                Middleware\ValidatePrismicWebhook::class => Container\Middleware\ValidatePrismicWebhookFactory::class,
                Middleware\ApiCacheBust::class => Container\Middleware\ApiCacheBustFactory::class,

                // Processes a preview token and redirects to the page being previewed
                Middleware\PreviewInitiator::class => Container\Middleware\PreviewInitiatorFactory::class,

                // Injects javascript for running A/B tests with Prismic and Google Analytics
                Middleware\ExperimentInitiator::class    => Container\Middleware\ExperimentInitiatorFactory::class,

                // Injects javascript to display the preview toolbar
                Middleware\InjectPreviewScript::class    => Container\Middleware\InjectPreviewScriptFactory::class,

                // Middleware that resolves the 404 document from the API
                Middleware\NotFoundSetup::class          => Container\Middleware\NotFoundSetupFactory::class,
                // Middleware that resolves the error document from the API
                Middleware\ErrorDocumentSetup::class     => Container\Middleware\ErrorDocumentSetupFactory::class,

                // The Pipeline used for rendering 404 error documents
                Middleware\NotFoundPipe::class           => Container\Middleware\NotFoundPipeFactory::class,
                // The Pipeline used for rendering exception/error documents
                Middleware\ErrorHandlerPipe::class       => Container\Middleware\ErrorHandlerPipeFactory::class,

                // Custom error response generator intended to replace the default in Zend Expressive
                Middleware\ErrorResponseGenerator::class => Container\Middleware\ErrorResponseGeneratorFactory::class,
            ],
            'invokables' => [
                // An instance used to track the current document for the request
                Service\CurrentDocument::class      => Service\CurrentDocument::class,
                // A simple request handler that returns a Json encoded success message for the webhook pipeline
                Handler\JsonSuccess::class          => Handler\JsonSuccess::class,
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
                 *
                 * This alias is commented out to show what to do to take advantage of CMS driven 404's
                 */
                NotFoundHandler::class => Middleware\NotFoundPipe::class,

                /**
                 * Replace the shipped ErrorResponseGenerator with our own in order to render 500 errors from the CMS
                 */
                ErrorResponseGenerator::class => Middleware\ErrorResponseGenerator::class,
            ],
            'delegators' => [
                Application::class => [
                    // Sets up routing for webhooks and previews
                    Container\PipelineAndRoutesDelegator::class,
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
                View\Helper\Url::class      => Container\View\Helper\UrlFactory::class,
                // View helper makes it easier to retrieve fragment values for the current document in the view
                View\Helper\Fragment::class => Container\View\Helper\FragmentFactory::class,
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
                // A Service name in the DI container that returns a \Psr\Cache\CacheItemPoolInterface
                'cache' => null,
            ],

            // Webhook Shared Secret
            'webhook_secret' => null,
            // URL where webhooks will get POSTed
            'webhook_url' => '/prismicio-cache-webhook',
            // URL for Previews
            'preview_url' => '/prismic-preview',

            /**
             * Error Handler configuration for content managed
             * error pages in production
             */
            'error_handler' => [

                /**
                 * If you have either piped the NotFoundPipe in your app config or replaced the default
                 * NotFoundHandler shipped in Expressive with the pipe, this is where you can provide the
                 * 'bookmark' for the 404 document and the template to render that document to.
                 */
                'bookmark_404'      => null,
                'template_404'      => 'error::404',

                /**
                 * The error/exception handler works in the same way as 404's
                 * We need a bookmark and a template to render
                 */
                'bookmark_error'    => null,
                'template_error'    => 'error::error',

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
             * This flag determines whether the JS is injected on every request or not
             * You'd want to se this to true if you want to take advantage of the
             * Edit Button feature of the standard JS
             */
            'alwaysInjectToolbar' => false,

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
