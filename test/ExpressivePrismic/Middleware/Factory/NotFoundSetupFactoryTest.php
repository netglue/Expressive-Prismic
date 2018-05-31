<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\NotFoundSetupFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\NotFoundSetup;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;

class NotFoundSetupFactoryTest extends TestCase
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
                'template_404' => 'templateName',
                'bookmark_404' => 'bookmarkName',
                'render_404_fallback' => true,
            ]]
        ]);

        $factory = new NotFoundSetupFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(NotFoundSetup::class, $middleware);
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionTrhownForEmptyBookmark()
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => ['error_handler' => [
                'template_404' => 'templateName',
                'bookmark_404' => null,
                'render_404_fallback' => true,
            ]]
        ]);

        $factory = new NotFoundSetupFactory;

        $middleware = $factory($this->container->reveal());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionTrhownForEmptyTemplate()
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => ['error_handler' => [
                'template_404' => null,
                'bookmark_404' => 'foo',
                'render_404_fallback' => true,
            ]]
        ]);

        $factory = new NotFoundSetupFactory;

        $middleware = $factory($this->container->reveal());
    }
}
