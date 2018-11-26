<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use ExpressivePrismic\Handler\JsonSuccess;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use Psr\Container\ContainerInterface;
use Zend\Expressive\MiddlewareFactory;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\MiddlewarePipeInterface;

class WebhookMiddlewarePipeFactory
{
    public function __invoke(ContainerInterface $container) : MiddlewarePipeInterface
    {
        /** @var MiddlewareFactory $factory */
        $factory = $container->get(MiddlewareFactory::class);
        $pipeline = new MiddlewarePipe();
        $pipeline->pipe($factory->prepare(ValidatePrismicWebhook::class));
        $pipeline->pipe($factory->prepare(ApiCacheBust::class));
        $pipeline->pipe($factory->prepare(JsonSuccess::class));
        return $pipeline;
    }
}
