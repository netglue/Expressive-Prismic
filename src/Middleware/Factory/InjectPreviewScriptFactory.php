<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Middleware\InjectPreviewScript;
use Zend\View\HelperPluginManager;

class InjectPreviewScriptFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : InjectPreviewScript
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }

        $helpers = $container->get(HelperPluginManager::class);
        $config  = $container->get('config');

        return new InjectPreviewScript(
            $helpers,
            $config['prismic']['toolbarScript'],
            $config['prismic']['endpointScript'],
            $config['prismic']['api']['url']
        );
    }

}
