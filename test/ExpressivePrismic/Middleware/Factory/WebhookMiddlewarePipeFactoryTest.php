<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware\Factory;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\Factory\WebhookMiddlewarePipeFactory;

// Deps
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Middleware\JsonSuccess;

class WebhookMiddlewarePipeFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get(ApiCacheBust::class)->willReturn(
            $this->prophesize(ApiCacheBust::class)->reveal()
        );
        $this->container->get(ValidatePrismicWebhook::class)->willReturn(
            $this->prophesize(ValidatePrismicWebhook::class)->reveal()
        );
        $this->container->get(JsonSuccess::class)->willReturn(
            $this->prophesize(JsonSuccess::class)->reveal()
        );

        $factory = new WebhookMiddlewarePipeFactory;

        $pipe = $factory($this->container->reveal());

        $this->assertInstanceOf(MiddlewarePipe::class, $pipe);
    }
}
