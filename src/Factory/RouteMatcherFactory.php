<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;
use ExpressivePrismic\RouteMatcher;

class RouteMatcherFactory
{
    public function __invoke(ContainerInterface $container) : RouteMatcher
    {
        $app = $container->get(Application::class);

        return new RouteMatcher(
            $app->getRoutes(),
            $container->get(RouteParams::class)
        );
    }
}
