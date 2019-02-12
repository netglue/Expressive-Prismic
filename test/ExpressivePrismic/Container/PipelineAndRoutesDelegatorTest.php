<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

use ExpressivePrismic\Container\PipelineAndRoutesDelegator;
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;

class PipelineAndRoutesDelegatorTest extends TestCase
{
    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testRoutesAreConfigured() : void
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
            function () use ($app) {
                return $app;
            }
        );

        $this->assertSame($app, $return);
    }
}
