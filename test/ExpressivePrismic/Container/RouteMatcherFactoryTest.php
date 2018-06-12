<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\RouteMatcherFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;
use ExpressivePrismic\RouteMatcher;

class RouteMatcherFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $app = $this->prophesize(Application::class);
        $app->getRoutes()->willReturn([]);
        $this->container->get(Application::class)->willReturn(
            $app->reveal()
        );
        $this->container->get(RouteParams::class)->willReturn(
            new RouteParams
        );


        $factory = new RouteMatcherFactory;
        $resolver = $factory($this->container->reveal());

        $this->assertInstanceOf(RouteMatcher::class, $resolver);
    }
}
