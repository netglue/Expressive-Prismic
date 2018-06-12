<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Helper\UrlHelper;

class LinkResolverFactory
{

    public function __invoke(ContainerInterface $container) : LinkResolver
    {
        /** @var Api $api */
        $api = $container->get('ExpressivePrismic\ApiClient');

        $resolver = new LinkResolver(
            $api->bookmarks(),
            $container->get(RouteParams::class),
            $container->get(UrlHelper::class),
            $container->get(RouteMatcher::class)
        );

        $api->setLinkResolver($resolver);

        return $resolver;
    }
}
