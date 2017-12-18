<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\Exception;
class ApiFactory
{

    public function __invoke(ContainerInterface $container) : Api
    {
        if (!$container->has('config')) {
            throw new Exception\RuntimeException('No configuration can be found in the DI Container');
        }

        $config = $container->get('config');

        if (!isset($config['prismic']['api'])) {
            throw new Exception\RuntimeException('No Prismic API configuration can be found');
        }

        $config = $config['prismic']['api'];

        $token = isset($config['token']) ? $config['token'] : null;
        $url   = isset($config['url'])   ? $config['url']   : null;
        $ttl   = isset($config['ttl'])   ? $config['ttl']   : null;

        if (!$url) {
            throw new Exception\RuntimeException('Prismic API endpoint URL must be specified in [prismic][api][url]');
        }

        // Retrieve a dedicated cache instance that implements Prismic's Cache Interface
        $cache = isset($config['cache']) ? $container->get($config['cache']) : null;

        try {
            return Api::get($url, $token, null, $cache, $ttl);
        } catch (\Exception $e) {
            throw new Exception\RuntimeException('Exception thrown creating API instance. Have you provided a valid API URL?', 0, null);
        }
    }
}
