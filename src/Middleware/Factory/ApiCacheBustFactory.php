<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ApiCacheBustFactory
{

    public function __invoke(ContainerInterface $container) : ApiCacheBust
    {
        $api = $container->get(Prismic\Api::class);
        $config = $container->get('config');
        $secret = isset($config['prismic']['webhook_secret']) ? $config['prismic']['webhook_secret'] : null;

        return new ApiCacheBust($api, $secret);
    }

}
