<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Middleware\Factory\ApiCacheBustFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\Middleware\ApiCacheBust;
use Psr\Cache\CacheItemPoolInterface;

class ApiCacheBustFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $api = $this->prophesize(Api::class);
        $cache = $this->prophesize(CacheItemPoolInterface::class)->reveal();
        $api->getCache()->willReturn($cache);

        $this->container->get(Api::class)->willReturn(
            $api->reveal()
        );

        $factory = new ApiCacheBustFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(ApiCacheBust::class, $middleware);
    }
}
