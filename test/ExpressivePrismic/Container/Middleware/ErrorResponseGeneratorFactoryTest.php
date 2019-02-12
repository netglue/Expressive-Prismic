<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ErrorResponseGeneratorFactory;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;
use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismic\Middleware\NotFoundPipe;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipeInterface;

class ErrorResponseGeneratorFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(ErrorHandlerPipe::class)->willReturn(
            $this->prophesize(MiddlewarePipeInterface::class)->reveal()
        );
        $this->container->get(NotFoundPipe::class)->willReturn(
            $this->prophesize(MiddlewarePipeInterface::class)->reveal()
        );
        $factory = new ErrorResponseGeneratorFactory;

        $handler = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorResponseGenerator::class, $handler);
    }
}
