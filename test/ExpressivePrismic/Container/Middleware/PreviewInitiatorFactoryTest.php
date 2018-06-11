<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\PreviewInitiatorFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\PreviewInitiator;
use Prismic;

class PreviewInitiatorFactoryTest extends TestCase
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

        $factory = new PreviewInitiatorFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(PreviewInitiator::class, $middleware);
    }
}
