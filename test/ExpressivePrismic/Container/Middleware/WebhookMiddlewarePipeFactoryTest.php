<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\WebhookMiddlewarePipeFactory;
use ExpressivePrismic\Handler\JsonSuccess;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\MiddlewarePipe;

class WebhookMiddlewarePipeFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
    {
        $middlewareFactory = $this->prophesize(MiddlewareFactory::class);

        $middlewareFactory->prepare(ApiCacheBust::class)->shouldBeCalled();
        $middlewareFactory->prepare(ValidatePrismicWebhook::class)->shouldBeCalled();
        $middlewareFactory->prepare(JsonSuccess::class)->shouldBeCalled();

        $this->container->get(MiddlewareFactory::class)->willReturn(
            $middlewareFactory->reveal()
        );

        $factory = new WebhookMiddlewarePipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }
}
