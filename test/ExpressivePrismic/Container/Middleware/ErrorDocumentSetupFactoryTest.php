<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ErrorDocumentSetupFactory;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Middleware\ErrorDocumentSetup;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Container\ContainerInterface;

class ErrorDocumentSetupFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => ['error_handler' => [
                'template_error' => 'templateName',
                'bookmark_error' => 'bookmarkName',
            ]]
        ]);

        $factory = new ErrorDocumentSetupFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorDocumentSetup::class, $middleware);
    }

    public function testExceptionThrownForEmptyBookmark() : void
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => ['error_handler' => [
                'template_error' => 'templateName',
                'bookmark_error' => null,
            ]]
        ]);

        $factory = new ErrorDocumentSetupFactory;
        $this->expectException(Exception\RuntimeException::class);
        $factory($this->container->reveal());
    }

    public function testExceptionThrownForEmptyTemplate() : void
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => ['error_handler' => [
                'template_error' => null,
                'bookmark_error' => 'foo',
            ]]
        ]);

        $factory = new ErrorDocumentSetupFactory;
        $this->expectException(Exception\RuntimeException::class);
        $factory($this->container->reveal());
    }
}
