<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\LinkResolverFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\RouteMatcher;
use Zend\Expressive\Helper\UrlHelper;

class LinkResolverFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testLinkResolverWillBeCreated()
    {
        $api = $this->prophesize(Api::class);
        $api->bookmarks()->willReturn([]);
        $this->container->get(Api::class)->willReturn(
            $api->reveal()
        );
        $this->container->get(RouteParams::class)->willReturn(
            $this->prophesize(RouteParams::class)->reveal()
        );
        $this->container->get(UrlHelper::class)->willReturn(
            $this->prophesize(UrlHelper::class)->reveal()
        );
        $this->container->get(RouteMatcher::class)->willReturn(
            $this->prophesize(RouteMatcher::class)->reveal()
        );

        $factory = new LinkResolverFactory;
        $resolver = $factory($this->container->reveal());

        $this->assertInstanceOf(LinkResolver::class, $resolver);
    }
}
