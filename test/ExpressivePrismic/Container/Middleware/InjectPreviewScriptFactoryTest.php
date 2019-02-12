<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismicTest\TestCase;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Container\Middleware\InjectPreviewScriptFactory;
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\InjectPreviewScript;
use Zend\View\HelperPluginManager;
use Prismic;

class InjectPreviewScriptFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
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

    public function testExceptionThrownWhenHelpersNotAvailable() : void
    {
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $factory = new InjectPreviewScriptFactory;
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('The Zend\View\HelperPluginManager cannot be located in the container');
        $factory($this->container->reveal());
    }
}
