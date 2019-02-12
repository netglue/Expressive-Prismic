<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Middleware\ExperimentInitiator;
use Zend\View\HelperPluginManager;

class ExperimentInitiatorFactory
{

    public function __invoke(ContainerInterface $container) : ExperimentInitiator
    {
        if (! $container->has(HelperPluginManager::class)) {
            throw new Exception\RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }

        $config  = $container->get('config');

        return new ExperimentInitiator(
            $container->get(Prismic\Api::class),
            $container->get(HelperPluginManager::class),
            $config['prismic']['toolbarScript'],
            $config['prismic']['endpointScript'],
            $config['prismic']['api']['url']
        );
    }
}
