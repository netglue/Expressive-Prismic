<?php
declare(strict_types=1);

namespace ExpressivePrismic\Service\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Service\MetaDataAutomator;
use Zend\View\HelperPluginManager;

class MetaDataAutomatorFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : MetaDataAutomator
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }
        $helpers = $container->get(HelperPluginManager::class);
        $config  = $container->get('config');
        $config  = isset($config['prismic']['head']) ? $config['prismic']['head'] : [];

        return new MetaDataAutomator($helpers, $config);
    }
}
