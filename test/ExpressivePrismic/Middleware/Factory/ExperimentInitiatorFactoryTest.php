<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\ExperimentInitiatorFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ExperimentInitiator;
use Zend\View\HelperPluginManager;
use Prismic;

class ExperimentInitiatorFactoryTest extends TestCase
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

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The Zend\View\HelperPluginManager cannot be located in the container
     */
    public function testExceptionThrownWhenHelpersNotAvailable()
    {
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $factory = new ExperimentInitiatorFactory;
        $factory($this->container->reveal());
    }
}
