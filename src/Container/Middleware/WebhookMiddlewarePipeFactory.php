<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;

use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Handler\JsonSuccess;

class WebhookMiddlewarePipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $pipeline = new MiddlewarePipe();
        $pipeline->pipe($container->get(ValidatePrismicWebhook::class));
        $pipeline->pipe($container->get(ApiCacheBust::class));
        $pipeline->pipe($container->get(JsonSuccess::class));
        return $pipeline;
    }
}