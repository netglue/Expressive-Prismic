<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ApiCacheBustFactory;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismicTest\TestCase;
use Prismic\Api;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

class ApiCacheBustFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
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
