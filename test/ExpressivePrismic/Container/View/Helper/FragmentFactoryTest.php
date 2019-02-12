<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\View\Helper;

use ExpressivePrismic\Container\View\Helper\FragmentFactory;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;

class FragmentFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $this->container->get(CurrentDocument::class)->willReturn(
            $this->prophesize(CurrentDocument::class)->reveal()
        );

        $factory = new FragmentFactory;

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(Fragment::class, $helper);
    }
}
