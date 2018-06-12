<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\ErrorDocumentSetupFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ErrorDocumentSetup;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;

class ErrorDocumentSetupFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
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

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionThrownForEmptyBookmark()
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
        $factory($this->container->reveal());
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionThrownForEmptyTemplate()
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
        $factory($this->container->reveal());
    }
}
