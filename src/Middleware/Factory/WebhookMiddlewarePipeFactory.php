<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use ExpressivePrismic\Middleware\ApiCacheBust;
use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;

class WebhookMiddlewarePipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $pipeline = new MiddlewarePipe();
        $pipeline->pipe($container->get(ApiCacheBust::class));
        return $pipeline;
    }

}
