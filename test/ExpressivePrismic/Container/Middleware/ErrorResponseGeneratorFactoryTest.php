<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismic\Middleware\NotFoundPipe;
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\ErrorResponseGeneratorFactory;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use Zend\Stratigility\MiddlewarePipeInterface;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;

class ErrorResponseGeneratorFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
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
