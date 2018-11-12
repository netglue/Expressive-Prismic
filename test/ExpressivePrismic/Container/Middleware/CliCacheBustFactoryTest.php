<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;


use ExpressivePrismic\Container\Middleware\CliCacheBustFactory;
use ExpressivePrismic\Middleware\CliCacheBust;
use ExpressivePrismicTest\TestCase;
use Prismic\Api;
use Psr\Container\ContainerInterface;

class CliCacheBustFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        parent::setUp();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Api::class)->willReturn(
            $this->prophesize(Api::class)->reveal()
        );
        $this->container = $container->reveal();
    }

    public function testFactoryReturnsMiddleware() : void
    {
        $factory = new CliCacheBustFactory();
        $middleware = $factory($this->container);
        $this->assertInstanceOf(CliCacheBust::class, $middleware);
    }
}
