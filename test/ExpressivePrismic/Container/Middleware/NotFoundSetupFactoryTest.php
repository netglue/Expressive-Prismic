<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\NotFoundSetupFactory;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Middleware\NotFoundSetup;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Container\ContainerInterface;

class NotFoundSetupFactoryTest extends TestCase
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
                'template_404' => 'templateName',
                'bookmark_404' => 'bookmarkName',
            ]]
        ]);

        $factory = new NotFoundSetupFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(NotFoundSetup::class, $middleware);
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
                'template_404' => 'templateName',
                'bookmark_404' => null,
            ]]
        ]);

        $factory = new NotFoundSetupFactory;
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
                'template_404' => null,
                'bookmark_404' => 'foo',
            ]]
        ]);

        $factory = new NotFoundSetupFactory;
        $this->expectException(Exception\RuntimeException::class);
        $factory($this->container->reveal());
    }
}
