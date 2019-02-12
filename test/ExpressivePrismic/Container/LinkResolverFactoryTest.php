<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

use ExpressivePrismic\Container\LinkResolverFactory;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;
use Prismic\Api;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;

class LinkResolverFactoryTest extends TestCase
{
    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testLinkResolverWillBeCreated() : void
    {
        $api = $this->prophesize(Api::class);
        $api->setLinkResolver(Argument::type(LinkResolver::class))->shouldBeCalled();
        $api->bookmarks()->willReturn([]);
        $this->container->get('ExpressivePrismic\ApiClient')->willReturn(
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
