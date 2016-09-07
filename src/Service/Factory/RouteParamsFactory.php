<?php
declare(strict_types=1);

namespace ExpressivePrismic\Service\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Service\RouteParams;

class RouteParamsFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : RouteParams
    {
        $config = $container->get('config');
        $options = isset($config['prismic']['route_params']) ? $config['prismic']['route_params'] : [];
        return new RouteParams($options);
    }
}
