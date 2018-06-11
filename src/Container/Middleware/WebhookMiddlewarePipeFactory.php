<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Expressive\MiddlewareFactory;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Handler\JsonSuccess;
use Zend\Stratigility\MiddlewarePipeInterface;

class WebhookMiddlewarePipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipeInterface
    {
        $factory = $container->get(MiddlewareFactory::class);
        $pipeline = new MiddlewarePipe();
        $pipeline->pipe($factory->prepare(ValidatePrismicWebhook::class));
        $pipeline->pipe($factory->prepare(ApiCacheBust::class));
        $pipeline->pipe($factory->prepare(JsonSuccess::class));
        return $pipeline;
    }
}
