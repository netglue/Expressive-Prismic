<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\View\Helper;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\View\Helper\UrlFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\View\Helper\Url;
use Prismic\Api;

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

        $factory = new UrlFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Url::class, $helper);
    }
}
