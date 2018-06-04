<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use Prismic\LinkResolver;
use Psr\Container\ContainerInterface;
use Prismic\Api;

class ApiFactory
{

    public function __invoke(ContainerInterface $container) : Api
    {
        $api = $container->get('ExpressivePrismic\ApiClient');
        $container->get(LinkResolver::class);
        return $api;
    }
}
