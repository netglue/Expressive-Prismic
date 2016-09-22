<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Service\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;

/**
 * Class RouteParamsFactory
 *
 * @package ExpressivePrismic\Service\Factory
 */
class RouteParamsFactory
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return RouteParams
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : RouteParams
    {
        $config  = $container->get('config');
        $options = isset($config['prismic']['route_params'])
                 ? $config['prismic']['route_params']
                 : [];

        return new RouteParams($options);
    }
}
