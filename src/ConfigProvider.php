<?php
declare(strict_types=1);

namespace ExpressivePrismic;
use Prismic;

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
                    Prismic\Api::class                     => Factory\ApiFactory::class,
                    // Default Link Resolver
                    LinkResolver::class                    => Factory\LinkResolverFactory::class,
                    // Mapping Route Parameters
                    Service\RouteParams::class             => Service\Factory\RouteParamsFactory::class,
                    // Middleware
                    Middleware\PrismicTemplate::class      => Middleware\Factory\PrismicTemplateFactory::class,
                    Middleware\DocumentResolver::class     => Middleware\Factory\DocumentResolverFactory::class,
                    Middleware\MetaDataAutomator::class    => Middleware\Factory\MetaDataAutomatorFactory::class,
                    Middleware\ApiCacheBust::class         => Middleware\Factory\ApiCacheBustFactory::class,
                    Middleware\SetCanonical::class         => Middleware\Factory\SetCanonicalFactory::class,
                    Middleware\PreviewInitiator::class     => Middleware\Factory\PreviewInitiatorFactory::class,
                    Middleware\InjectPreviewScript::class  => Middleware\Factory\InjectPreviewScriptFactory::class,
                ],
                'invokables' => [

                ],
                'aliases' => [
                    Prismic\LinkResolver::class         => LinkResolver::class,
                ],
            ],

            /**
             * View Helpers
             */
            'view_helpers' => [
                'factories' => [
                    View\Helper\Url::class => View\Helper\Factory\UrlFactory::class,
                ],
                'aliases' => [
                    'prismicUrl' => View\Helper\Url::class,
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
                 * @see ExpressivePrismic\Service\RouteParams
                 */
                'route_params' => [
                    //'id'       => 'prismic-bookmark',
                    //'bookmark' => 'prismic-id',
                    //'uid'      => 'prismic-uid',
                    //'type'     => 'prismic-type',
                ],

                /**
                 * Automatic but naive retrieval of various head meta tags and elements
                 */
                'head' => [
                    /**
                     * A map where <meta> name -> document property, without the type prefix
                     * ie. to achieve <meta name="description" content="foo">, you would set
                     * 'description' => 'my_property', not 'my_type.my_property'
                     *
                     * Acceptable meta tags can be found in ExpressivePrismic\View\MetaDataExtractor
                     */
                    'meta_data_map' => [
                        // For example…
                        //'description' => 'meta_description',
                        //'keywords' => 'meta_keywords',
                        //'robots' => 'meta_robots',
                    ],
                    /**
                     * Setting the head title is a little more flexible,
                     * You can provide an array of document properties to search in order of preference
                     */
                    'title_search' => [
                        // For example…
                        // 'head_title',
                        // 'meta_title',
                        // 'title'
                        // etc…
                    ],
                    /**
                     * As with normal meta tags, but specific to Twitter Cards
                     */
                    'twitter_map' => [
                        // 'twitter:card' => 'my_card_type_property',
                        // 'twitter:title' => 'twitter_title',
                    ],
                    /**
                     * Open Graph
                     */
                    'og_map' => [
                        // 'og:title' => 'my_title_property',
                        // etc…
                    ],

                ],

            ],
        ];
    }

}
