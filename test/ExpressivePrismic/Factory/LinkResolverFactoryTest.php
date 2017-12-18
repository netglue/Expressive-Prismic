<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Factory;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Factory\LinkResolverFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Application;

class LinkResolverFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testLinkResolverWillBeCreated()
    {
        $this->container->get(Api::class)->willReturn(
            $this->prophesize(Api::class)->reveal()
        );
        $this->container->get(RouteParams::class)->willReturn(
            $this->prophesize(RouteParams::class)->reveal()
        );
        $this->container->get(UrlHelper::class)->willReturn(
            $this->prophesize(UrlHelper::class)->reveal()
        );
        $this->container->get(Application::class)->willReturn(
            $this->prophesize(Application::class)->reveal()
        );

        $factory = new LinkResolverFactory;
        $resolver = $factory($this->container->reveal());

        $this->assertInstanceOf(LinkResolver::class, $resolver);
    }
}
