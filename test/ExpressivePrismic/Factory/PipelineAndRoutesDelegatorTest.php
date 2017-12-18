<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Factory\PipelineAndRoutesDelegator;

// Deps

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;

class PipelineAndRoutesDelegatorTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testRoutesAreConfigured()
    {
        $app = $this->prophesize(Application::class);
        $app->route(
            Argument::type('string'),
            Argument::type('array'),
            Argument::type('array'),
            Argument::type('string')
        )->shouldBeCalledTimes(2);

        $app = $app->reveal();

        $factory = new PipelineAndRoutesDelegator;

        $return = $factory(
            $this->container->reveal(),
            'SomeName',
            function() use ($app) {
                return $app;
            }
        );

        $this->assertSame($app, $return);
    }
}
