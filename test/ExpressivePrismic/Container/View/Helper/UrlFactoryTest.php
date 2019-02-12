<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\View\Helper;

use ExpressivePrismic\Container\View\Helper\UrlFactory;
use ExpressivePrismic\View\Helper\Url;
use ExpressivePrismicTest\TestCase;
use Prismic\Api;
use Psr\Container\ContainerInterface;

class UrlFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(Api::class)->willReturn(
            $this->prophesize(Api::class)->reveal()
        );

        $factory = new UrlFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Url::class, $helper);
    }
}
