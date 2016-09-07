<?php
declare(strict_types=1);

namespace ExpressivePrismic\Factory;

use Interop\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Helper\UrlHelper;

class LinkResolverFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : LinkResolver
    {
        $api    = $container->get(Prismic\Api::class);
        $params = $container->get(RouteParams::class);
        $config = $container->get('config');
        $urlHelper = $container->get(UrlHelper::class);
        return new LinkResolver($api, $params, $config['routes'], $urlHelper);
    }
}
