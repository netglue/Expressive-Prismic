<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\DocumentResolverFactory;
use ExpressivePrismic\Middleware\DocumentResolver;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Container\ContainerInterface;

class DocumentResolverFactoryTest extends TestCase
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
