<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\ErrorHandlerFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ErrorHandler;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewarePipe;

class ErrorHandlerFactoryTest extends TestCase
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
                    'template_404'      => 'error::404',
                    'template_error'    => 'error::error',
                    'template_fallback' => 'error::prismic-fallback',
                    'layout_fallback'   => 'layout::error-fallback',
                    'bookmark_404'      => 'some-404',
                    'bookmark_error'    => 'some-500',
                ]
            ]
        ]);

        $this->container->get(ErrorHandlerPipe::class)->willReturn(
            $this->prophesize(MiddlewarePipe::class)->reveal()
        );
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(TemplateRendererInterface::class)->willReturn(
            $this->prophesize(TemplateRendererInterface::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );

        $factory = new ErrorHandlerFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorHandler::class, $pipe);
    }

}
