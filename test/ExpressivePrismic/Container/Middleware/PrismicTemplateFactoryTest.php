<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\PrismicTemplateFactory;

// Deps
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use ExpressivePrismic\Middleware\PrismicTemplate;
use Prismic\LinkResolver;

class PrismicTemplateFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(TemplateRendererInterface::class)->willReturn(
            $this->prophesize(TemplateRendererInterface::class)->reveal()
        );
        $this->container->get(LinkResolver::class)->willReturn(
            $this->prophesize(LinkResolver::class)->reveal()
        );

        $factory = new PrismicTemplateFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(PrismicTemplate::class, $middleware);
    }
}
