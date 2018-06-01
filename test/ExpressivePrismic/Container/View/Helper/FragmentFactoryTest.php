<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\View\Helper;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\View\Helper\FragmentFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismic\Service\CurrentDocument;

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

        $factory = new FragmentFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Fragment::class, $helper);
    }
}
