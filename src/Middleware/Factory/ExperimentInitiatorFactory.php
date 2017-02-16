<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\ExperimentInitiator;
use Zend\View\HelperPluginManager;

class ExperimentInitiatorFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ExperimentInitiator
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }

        $config  = $container->get('config');

        return new ExperimentInitiator(
            $container->get(Prismic\Api::class),
            $container->get(HelperPluginManager::class),
            $config['prismic']['api']['url'],
            $config['prismic']['endpointScript'],
            $config['prismic']['toolbarScript']
        );
    }
}
