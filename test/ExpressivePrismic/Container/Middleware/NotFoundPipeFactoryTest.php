<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\NotFoundPipeFactory;

// Deps
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Expressive\MiddlewareFactory;
use ExpressivePrismic\Middleware;
use ExpressivePrismic\Handler;

class NotFoundPipeFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $middlewareFactory = $this->prophesize(MiddlewareFactory::class);

        $middlewareFactory->prepare(Middleware\ExperimentInitiator::class)->shouldBeCalled();
        $middlewareFactory->prepare(Middleware\InjectPreviewScript::class)->shouldBeCalled();
        $middlewareFactory->prepare(Middleware\NotFoundSetup::class)->shouldBeCalled();
        $middlewareFactory->prepare(Handler\PrismicTemplate::class)->shouldBeCalled();

        $this->container->get(MiddlewareFactory::class)->willReturn(
            $middlewareFactory->reveal()
        );

        $factory = new NotFoundPipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }
}
