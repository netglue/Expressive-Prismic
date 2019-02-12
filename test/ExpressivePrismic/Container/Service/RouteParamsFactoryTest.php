<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Service;

use ExpressivePrismic\Container\Service\RouteParamsFactory;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;

class RouteParamsFactoryTest extends TestCase
{
    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithNoConfig() : void
    {
        $this->container->get('config')->willReturn([]);


        $factory = new RouteParamsFactory;
        $service = $factory($this->container->reveal());

        $this->assertInstanceOf(RouteParams::class, $service);

        $this->assertSame('prismic-uid', $service->getUid());
    }

    public function testFactoryWithConfig() : void
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'route_params' => [
                    'uid' => 'something-else'
                ]
            ]
        ]);

        $factory = new RouteParamsFactory;
        $service = $factory($this->container->reveal());

        $this->assertInstanceOf(RouteParams::class, $service);

        $this->assertSame('something-else', $service->getUid());
    }
}
