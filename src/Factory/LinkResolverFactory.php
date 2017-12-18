<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Psr\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Application;

class LinkResolverFactory
{

    public function __invoke(ContainerInterface $container) : LinkResolver
    {
        $api       = $container->get(Prismic\Api::class);
        $params    = $container->get(RouteParams::class);
        $urlHelper = $container->get(UrlHelper::class);
        $app       = $container->get(Application::class);

        return new LinkResolver($api, $params, $urlHelper, $app);
    }
}
