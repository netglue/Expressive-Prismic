<?php
declare(strict_types = 1);

namespace ExpressivePrismic;
use Prismic;

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
            'dependencies' => [
                'factories' => [
                    // Api Instance
                    Prismic\Api::class                              => Factory\ApiFactory::class,
                    // Default Link Resolver
                    LinkResolver::class                             => Factory\LinkResolverFactory::class,
                    // Mapping Route Parameters
                    Service\RouteParams::class                      => Service\Factory\RouteParamsFactory::class,

                    // Middleware
                    Middleware\PrismicTemplate::class               => Middleware\Factory\PrismicTemplateFactory::class,
                    Middleware\DocumentResolver::class              => Middleware\Factory\DocumentResolverFactory::class,
                    Middleware\ApiCacheBust::class                  => Middleware\Factory\ApiCacheBustFactory::class,
                    Middleware\PreviewInitiator::class              => Middleware\Factory\PreviewInitiatorFactory::class,
                    Middleware\InjectPreviewScript::class           => Middleware\Factory\InjectPreviewScriptFactory::class,
                    Middleware\ErrorHandler::class                  => Middleware\Factory\ErrorHandlerFactory::class,
                ],
                'invokables' => [
                    // An instance used to track the current document for the request
                    Service\CurrentDocument::class         => Service\CurrentDocument::class,
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
            ],

            /**
             * View Helpers
             */
            'view_helpers' => [
                'factories' => [
                    View\Helper\Url::class      => View\Helper\Factory\UrlFactory::class,
                    View\Helper\Fragment::class => View\Helper\Factory\FragmentFactory::class,
                ],
                'aliases' => [
                    'prismicUrl' => View\Helper\Url::class,
                    'fragment'   => View\Helper\Fragment::class,
                ],
            ],

            'routes' => [
                'prismic-cache-webhook' => [
                    'name' => 'prismic-webhook-cache-bust',
                    'path' => '/prismicio-cache-webhook',
                    'allowed_methods' => ['POST'],
                    'middleware' => [
                        Middleware\ApiCacheBust::class,
                    ],
                    'options' => [
                        'defaults' => [
                            'expectedSecret' => null,
                        ],
                    ],
                ],
                'prismic-preview' => [
                    'name' => 'prismic-preview',
                    'path' => '/prismic-preview',
                    'allowed_methods' => ['GET'],
                    'middleware' => [
                        Middleware\PreviewInitiator::class,
                    ],
                    'options' => [
                        'defaults' => [
                            'expectedSecret' => null,
                        ],
                    ],
                ],
            ],

            'prismic' => [
                // Api Connection Params
                'api' => [
                    // Permanent Access Token
                    'token' => null,
                    // Api Endpoint
                    'url' => null,
                    // Api Cache TTL in seconds. Set to 0 to cache forever (recommended)
                    'ttl' => null,
                ],

                /**
                 * Error Handler configuration for content managed
                 * error pages in production
                 */
                'error_handler' => [
                    'template_404'   => 'error::404',
                    'template_error' => 'error::error',
                    'bookmark_404'   => null,
                    'bookmark_error' => null,
                ],

                /**
                 * URL of the Prismic toolbar Javascript
                 */
                'toolbarScript' => '//static.cdn.prismic.io/prismic.min.js',

                /**
                 * Format of edit button initialisation script
                 */
                'editScript' => 'window.prismic = { endpoint: \'%s\' };',

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

            ],
        ];
    }

}
