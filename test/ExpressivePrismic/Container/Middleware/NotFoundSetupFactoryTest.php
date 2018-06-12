<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\NotFoundSetupFactory;

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
            ]]
        ]);

        $factory = new NotFoundSetupFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(NotFoundSetup::class, $middleware);
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
                'template_404' => 'templateName',
                'bookmark_404' => null,
            ]]
        ]);

        $factory = new NotFoundSetupFactory;
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
                'template_404' => null,
                'bookmark_404' => 'foo',
            ]]
        ]);

        $factory = new NotFoundSetupFactory;
        $factory($this->container->reveal());
    }
}
