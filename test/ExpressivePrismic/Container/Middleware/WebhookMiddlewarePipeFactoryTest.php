<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismicTest\TestCase;
use ExpressivePrismic\Container\Middleware\WebhookMiddlewarePipeFactory;
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Handler\JsonSuccess;
use Zend\Expressive\MiddlewareFactory;

class WebhookMiddlewarePipeFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
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
