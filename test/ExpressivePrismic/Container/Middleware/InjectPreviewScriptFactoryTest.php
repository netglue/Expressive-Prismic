<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\Middleware\Factory\RuntimeException;
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\InjectPreviewScriptFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\InjectPreviewScript;
use Zend\View\HelperPluginManager;
use Prismic;

class InjectPreviewScriptFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(Prismic\Api::class)->willReturn(
            $this->prophesize(Prismic\Api::class)->reveal()
        );
        $this->container->get('config')->willReturn([
            'prismic' => [
                'toolbarScript' => 'foo',
                'endpointScript' => 'foo',
                'api' => ['url' => 'foo'],
                'alwaysInjectToolbar' => false,
            ],
        ]);
        $this->container->has(HelperPluginManager::class)->willReturn(true);
        $this->container->get(HelperPluginManager::class)->willReturn(
            $this->prophesize(HelperPluginManager::class)->reveal()
        );

        $factory = new InjectPreviewScriptFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(InjectPreviewScript::class, $middleware);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The Zend\View\HelperPluginManager cannot be located in the container
     */
    public function testExceptionThrownWhenHelpersNotAvailable()
    {
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $factory = new InjectPreviewScriptFactory;
        $factory($this->container->reveal());
    }
}
