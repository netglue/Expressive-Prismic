<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\Middleware\ApiCacheBust;

/**
 * Middleware Factory
 *
 * @package ExpressivePrismic\Middleware\Factory
 */
class ApiCacheBustFactory
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return ApiCacheBust
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ApiCacheBust
    {
        $api = $container->get(Prismic\Api::class);
        $config = $container->get('config');
        $secret = isset($config['prismic']['webhook_secret']) ? $config['prismic']['webhook_secret'] : null;

        return new ApiCacheBust($api, $secret);
    }

}
