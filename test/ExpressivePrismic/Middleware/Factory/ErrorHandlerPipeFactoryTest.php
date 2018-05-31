<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\ErrorHandlerPipeFactory;

// Deps
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ErrorHandlerPipeFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'error_handler' => [
                    'middleware_error' => [
                        ApiCacheBust::class,
                    ]
                ]
            ]
        ]);

        $this->container->get(ApiCacheBust::class)->willReturn(
            $this->prophesize(ApiCacheBust::class)->reveal()
        );

        $factory = new ErrorHandlerPipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionIsThrownForInvalidPipe()
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'error_handler' => [
                    'middleware_error' => 'foo'
                ]
            ]
        ]);
        $factory = new ErrorHandlerPipeFactory;
        $factory($this->container->reveal());
    }
}
