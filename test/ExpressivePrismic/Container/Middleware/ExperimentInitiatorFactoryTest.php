<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ExperimentInitiatorFactory;
use ExpressivePrismic\Exception\RuntimeException;
use ExpressivePrismic\Middleware\ExperimentInitiator;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Container\ContainerInterface;
use Zend\View\HelperPluginManager;

class ExperimentInitiatorFactoryTest extends TestCase
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
            ],
        ]);
        $this->container->has(HelperPluginManager::class)->willReturn(true);
        $this->container->get(HelperPluginManager::class)->willReturn(
            $this->prophesize(HelperPluginManager::class)->reveal()
        );

        $factory = new ExperimentInitiatorFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(ExperimentInitiator::class, $middleware);
    }

    public function testExceptionThrownWhenHelpersNotAvailable() : void
    {
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $factory = new ExperimentInitiatorFactory;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Zend\View\HelperPluginManager cannot be located in the container');
        $factory($this->container->reveal());
    }
}
