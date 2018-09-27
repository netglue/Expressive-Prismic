<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

use ExpressivePrismic\Container\RouteMatcherFactory;
use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouteCollector;

class RouteMatcherFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        /** @var RouteCollector|ObjectProphecy $collector */
        $collector = $this->prophesize(RouteCollector::class);
        $collector->getRoutes()->willReturn([]);
        $this->container->get(RouteCollector::class)->willReturn(
            $collector->reveal()
        );
        $this->container->get(RouteParams::class)->willReturn(
            new RouteParams
        );


        $factory = new RouteMatcherFactory;
        $resolver = $factory($this->container->reveal());

        $this->assertInstanceOf(RouteMatcher::class, $resolver);
    }
}
