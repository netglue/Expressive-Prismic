<?php
declare(strict_types = 1);

namespace ExpressivePrismic;
use Prismic;
use Zend\Expressive\Application;

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
                Prismic\Api::class                              => Factory\ApiFactory::class,

                // Default Link Resolver
                LinkResolver::class                             => Factory\LinkResolverFactory::class,

                // Mapping Route Parameters
                Service\RouteParams::class                      => Service\Factory\RouteParamsFactory::class,

                // Middleware

                // For rendering document based templates
                Middleware\PrismicTemplate::class               => Middleware\Factory\PrismicTemplateFactory::class,

                // Sets the matched document as a request attribute
                Middleware\DocumentResolver::class              => Middleware\Factory\DocumentResolverFactory::class,

                // A Midleware Pipeline containing the cache busting middleware that you can manipulate with a delegator factory
                Middleware\WebhookMiddlewarePipe::class         => Middleware\Factory\WebhookMiddlewarePipeFactory::class,
                // Processes Webhooks from Prismic.io and busts the cache
                Middleware\ApiCacheBust::class                  => Middleware\Factory\ApiCacheBustFactory::class,

                // Processes a preview token and redirects to the page being previewed
                Middleware\PreviewInitiator::class              => Middleware\Factory\PreviewInitiatorFactory::class,

                // Injects javascript for running A/B tests with Prismic and Google Analytics
                Middleware\ExperimentInitiator::class           => Middleware\Factory\ExperimentInitiatorFactory::class,

                // Injects javascript to display the preview toolbar
                Middleware\InjectPreviewScript::class           => Middleware\Factory\InjectPreviewScriptFactory::class,

                // Provides an error handler that can render pretty 404's and server errors
                Middleware\ErrorHandler::class                  => Middleware\Factory\ErrorHandlerFactory::class,
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
                // The names of 2 templates to render for 404's and 500 errors
                'template_404'      => 'error::404',
                'template_error'    => 'error::error',
                // The layout and template to render when an exception is thrown trying to render the error documents
                'template_fallback' => 'error::prismic-fallback',
                'layout_fallback'   => 'layout::error-fallback',
                // The bookmarks for the Prismic.io documents used to render the 404 and 500 errors
                'bookmark_404'      => null,
                'bookmark_error'    => null,
                // Used to create the middleware Pipe that the error requests goes through prior to rendering
                'middleware' => [
                    Middleware\ExperimentInitiator::class,
                    Middleware\InjectPreviewScript::class,
                ],
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
