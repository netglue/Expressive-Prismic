<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\ErrorHandlerPipeFactory;

// Deps
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Expressive\MiddlewareFactory;
use ExpressivePrismic\Middleware as AppMiddleware;

class ErrorHandlerPipeFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $middlewareFactory = $this->prophesize(MiddlewareFactory::class);

        $middlewareFactory->prepare(AppMiddleware\ExperimentInitiator::class)->shouldBeCalled();
        $middlewareFactory->prepare(AppMiddleware\InjectPreviewScript::class)->shouldBeCalled();
        $middlewareFactory->prepare(AppMiddleware\ErrorDocumentSetup::class)->shouldBeCalled();
        $middlewareFactory->prepare(AppMiddleware\PrismicTemplate::class)->shouldBeCalled();

        $this->container->get(MiddlewareFactory::class)->willReturn(
            $middlewareFactory->reveal()
        );

        $factory = new ErrorHandlerPipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }
}
