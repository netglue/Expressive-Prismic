<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

use ExpressivePrismic\Container\PipelineAndRoutesDelegator;
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;

class PipelineAndRoutesDelegatorTest extends TestCase
{
    /** @var ContainerInterface&ObjectProphecy */
    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testRoutesAreConfigured() : void
    {
        $app = $this->prophesize(Application::class);
        $app->route(
            '/webhook',
            Argument::type('array'),
            Argument::type('array'),
            Argument::type('string')
        )->shouldBeCalled();
        $app->route(
            '/preview',
            Argument::type('array'),
            Argument::type('array'),
            Argument::type('string')
        )->shouldBeCalled();

        $app = $app->reveal();

        $factory = new PipelineAndRoutesDelegator;

        $this->container->get('config')->shouldBeCalled()->willReturn([
            'prismic' => [
                'webhook_url' => '/webhook',
                'preview_url' => '/preview',
            ],
        ]);

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
