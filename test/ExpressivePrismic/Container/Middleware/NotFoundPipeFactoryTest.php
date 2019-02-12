<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\NotFoundPipeFactory;
use ExpressivePrismic\Middleware;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\MiddlewarePipe;

class NotFoundPipeFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $middlewareFactory = $this->prophesize(MiddlewareFactory::class);

        $middlewareFactory->prepare(Middleware\ExperimentInitiator::class)->shouldBeCalled();
        $middlewareFactory->prepare(Middleware\InjectPreviewScript::class)->shouldBeCalled();
        $middlewareFactory->prepare(Middleware\NotFoundSetup::class)->shouldBeCalled();
        $middlewareFactory->prepare(Middleware\PrismicTemplate::class)->shouldBeCalled();

        $this->container->get(MiddlewareFactory::class)->willReturn(
            $middlewareFactory->reveal()
        );

        $factory = new NotFoundPipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }
}
