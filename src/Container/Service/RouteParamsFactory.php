<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container\Service;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Service\RouteParams;

/**
 * Class RouteParamsFactory
 *
 * @package ExpressivePrismic\Service\Factory
 */
class RouteParamsFactory
{

    public function __invoke(ContainerInterface $container) : RouteParams
    {
        $config  = $container->get('config');
        $options = isset($config['prismic']['route_params'])
                 ? $config['prismic']['route_params']
                 : [];

        return new RouteParams($options);
    }
}
