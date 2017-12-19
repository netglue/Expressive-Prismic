<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Prismic\Api;
use Prismic\Cache\CacheInterface;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ApiCacheBustFactory
{

    public function __invoke(ContainerInterface $container) : ApiCacheBust
    {
        $api = $container->get(Api::class);
        return new ApiCacheBust($api->getCache());
    }

}
