<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouteCollector;

class RouteMatcherFactory
{
    public function __invoke(ContainerInterface $container) : RouteMatcher
    {
        return new RouteMatcher(
            $container->get(RouteCollector::class),
            $container->get(RouteParams::class)
        );
    }
}
