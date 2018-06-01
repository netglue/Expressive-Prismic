<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ApiCacheBustFactory
{

    public function __invoke(ContainerInterface $container) : ApiCacheBust
    {
        $api = $container->get(Api::class);
        return new ApiCacheBust($api->getCache());
    }
}
