<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use Psr\Container\ContainerInterface;
use Prismic\Api;
use Prismic\Exception\ExceptionInterface as PrismicException;
use ExpressivePrismic\Exception;

class ApiClientFactory
{

    public function __invoke(ContainerInterface $container) : Api
    {
        if (! $container->has('config')) {
            throw new Exception\RuntimeException('No configuration can be found in the DI Container');
        }

        /** @var array $config */
        $config = $container->get('config');

        if (! isset($config['prismic']['api'])) {
            throw new Exception\RuntimeException('No Prismic API configuration can be found');
        }

        $config = $config['prismic']['api'];

        $token = isset($config['token']) ? (string) $config['token'] : null;
        $url   = isset($config['url']) ? (string) $config['url'] : null;

        if (! $url) {
            throw new Exception\RuntimeException(
                'Prismic API endpoint URL must be specified in [prismic][api][url]'
            );
        }

        // Retrieve a dedicated cache instance that implements Prismic's Cache Interface
        $cache = isset($config['cache']) ? $container->get($config['cache']) : null;
        return Api::get($url, $token, null, $cache);
    }
}
