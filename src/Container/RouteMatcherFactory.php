<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;
use ExpressivePrismic\RouteMatcher;
use Zend\Expressive\Router\RouteCollector;

class RouteMatcherFactory
{
    public function __invoke(ContainerInterface $container) : RouteMatcher
    {
        /** @var RouteCollector $collector */
        $collector = $container->get(RouteCollector::class);

        return new RouteMatcher(
            $collector->getRoutes(),
            $container->get(RouteParams::class)
        );
    }
}
