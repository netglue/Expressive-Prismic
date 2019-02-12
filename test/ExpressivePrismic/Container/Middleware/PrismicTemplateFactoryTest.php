<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\PrismicTemplateFactory;
use ExpressivePrismic\Middleware\PrismicTemplate;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class PrismicTemplateFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(TemplateRendererInterface::class)->willReturn(
            $this->prophesize(TemplateRendererInterface::class)->reveal()
        );

        $factory = new PrismicTemplateFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(PrismicTemplate::class, $middleware);
    }
}
