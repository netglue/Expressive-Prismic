<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\View\Helper\Factory\UrlFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\View\Helper\Url;
use Prismic\Api;
use Prismic\LinkResolver;

class UrlFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(Api::class)->willReturn(
            $this->prophesize(Api::class)->reveal()
        );
        $this->container->get(LinkResolver::class)->willReturn(
            $this->prophesize(LinkResolver::class)->reveal()
        );

        $factory = new UrlFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Url::class, $helper);
    }
}
