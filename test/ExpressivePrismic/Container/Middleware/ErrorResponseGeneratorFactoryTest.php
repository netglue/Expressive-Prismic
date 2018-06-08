<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\Middleware\Factory\ExpressivePrismic;
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\ErrorResponseGeneratorFactory;


use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismic\Service\CurrentDocument;
use Zend\Stratigility\MiddlewarePipeInterface;

class ErrorResponseGeneratorFactoryTest extends TestCase
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
                    'template_error' => 'template',
                    'bookmark_error' => 'bookmark',
                ]
            ]
        ]);

        $this->container->get('ExpressivePrismic\Middleware\ErrorHandlerPipe')->willReturn(
            $this->prophesize(MiddlewarePipeInterface::class)->reveal()
        );
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );

        $factory = new ErrorResponseGeneratorFactory;

        $handler = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorResponseGenerator::class, $handler);
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionIsThrownForEmptyTemplate()
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'error_handler' => [
                    'template_error' => null,
                    'bookmark_error' => 'bookmark',
                ]
            ]
        ]);
        $factory = new ErrorResponseGeneratorFactory;
        $factory($this->container->reveal());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionIsThrownForEmptyBookmark()
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'error_handler' => [
                    'template_error' => 'template',
                    'bookmark_error' => null,
                ]
            ]
        ]);
        $factory = new ErrorResponseGeneratorFactory;
        $factory($this->container->reveal());
    }
}
