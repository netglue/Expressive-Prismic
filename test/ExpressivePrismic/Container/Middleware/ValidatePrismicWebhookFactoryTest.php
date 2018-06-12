<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Container\Middleware\ValidatePrismicWebhookFactory;

// Deps
use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use Prismic\Cache\CacheInterface;

class ValidatePrismicWebhookFactoryTest extends TestCase
{

    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn([
            'prismic' => [
                'webhook_secret' => 'foo'
            ]
        ]);

        $factory = new ValidatePrismicWebhookFactory;

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(ValidatePrismicWebhook::class, $middleware);
    }
}
