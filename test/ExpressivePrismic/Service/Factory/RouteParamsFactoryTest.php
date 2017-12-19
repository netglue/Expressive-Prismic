<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Service\Factory;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Service\Factory\RouteParamsFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;

class RouteParamsFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithNoConfig()
    {
        $this->container->get('config')->willReturn([]);


        $factory = new RouteParamsFactory;
        $service = $factory($this->container->reveal());

        $this->assertInstanceOf(RouteParams::class, $service);

        $this->assertSame('prismic-uid', $service->getUid());
    }

    public function testFactoryWithConfig()
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
