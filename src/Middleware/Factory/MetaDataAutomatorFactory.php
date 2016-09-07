<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Middleware\MetaDataAutomator;
use Zend\View\HelperPluginManager;
use ExpressivePrismic\View\MetaDataExtractor;

class MetaDataAutomatorFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : MetaDataAutomator
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }
        $helpers   = $container->get(HelperPluginManager::class);

        $config = $container->get('config');
        $config = isset($config['prismic']['head']) ? $config['prismic']['head'] : [];

        $extractor = $container->get(MetaDataExtractor::class);
        return new MetaDataAutomator($helpers, $config);
    }
}
