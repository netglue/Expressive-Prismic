<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\DocumentResolverFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\Middleware\DocumentResolver;

class DocumentResolverFactoryTest extends TestCase
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
        $this->container->get(RouteParams::class)->willReturn(
            new RouteParams
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );

        $factory = new DocumentResolverFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(DocumentResolver::class, $middleware);
    }
}
