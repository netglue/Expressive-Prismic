<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\ApiCacheBustFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ApiCacheBustFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => [
                'webhook_secret' => 'foo',
            ],
        ]);

        $factory = new ApiCacheBustFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(ApiCacheBust::class, $middleware);
    }
}
