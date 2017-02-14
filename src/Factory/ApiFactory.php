<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Interop\Container\ContainerInterface;
use Prismic\Api;
use Zend\Session;

/**
 * Factory for the Prismic\Api
 *
 * @package ExpressivePrismic\Factory
 */
class ApiFactory
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return Api
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Api
    {
        if (!$container->has('config')) {
            throw new \RuntimeException('No configuration can be found in the DI Container');
        }

        $config = $container->get('config');

        if (!isset($config['prismic']['api'])) {
            throw new \RuntimeException('No Prismic API configuration can be found');
        }

        $config = $config['prismic']['api'];

        $token = isset($config['token']) ? $config['token'] : null;
        $url   = isset($config['url'])   ? $config['url']   : null;
        $ttl   = isset($config['ttl'])   ? $config['ttl']   : null;

        if (!$url) {
            throw new \RuntimeException('Prismic API endpoint URL must be specified in [prismic][api][url]');
        }

        // Retrieve a dedicated cache instance that implements Prismic's Cache Interface
        $cache = isset($config['cache']) ? $container->get($config['cache']) : null;

        // Use either the configured token, or a preview token retrieved from the session

        return Api::get($url, $token, null, $cache, $ttl);
    }
}
