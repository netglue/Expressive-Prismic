<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\ApiCacheBust;

class ApiCacheBustFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ApiCacheBust
    {
        $api = $container->get(Prismic\Api::class);
        return new ApiCacheBust($api);
    }

}
