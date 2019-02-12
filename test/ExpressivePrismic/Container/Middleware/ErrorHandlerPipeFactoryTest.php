<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ErrorHandlerPipeFactory;
use ExpressivePrismic\Middleware as AppMiddleware;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\MiddlewarePipe;

class ErrorHandlerPipeFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
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
