<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use ExpressivePrismic\Middleware\CliCacheBust;
use Prismic\Api;
use Psr\Container\ContainerInterface;

class CliCacheBustFactory
{
    public function __invoke(ContainerInterface $container) : CliCacheBust
    {
        return new CliCacheBust($container->get(Api::class));
    }
}
