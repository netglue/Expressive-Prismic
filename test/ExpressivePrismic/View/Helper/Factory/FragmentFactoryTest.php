<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\View\Helper\Factory\FragmentFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic\LinkResolver;

class FragmentFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );
        $this->container->get(LinkResolver::class)->willReturn(
            $this->prophesize(LinkResolver::class)->reveal()
        );

        $factory = new FragmentFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Fragment::class, $helper);
    }
}
