<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container\Middleware;

use ExpressivePrismic\Container\Middleware\ValidatePrismicWebhookFactory;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;

class ValidatePrismicWebhookFactoryTest extends TestCase
{

    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory() : void
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
