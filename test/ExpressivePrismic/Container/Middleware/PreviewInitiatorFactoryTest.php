<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\PreviewInitiatorFactory;
use ExpressivePrismic\Middleware\PreviewInitiator;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Container\ContainerInterface;

class PreviewInitiatorFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );

        $factory = new PreviewInitiatorFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(PreviewInitiator::class, $middleware);
    }
}
