<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\InjectPreviewScript;
use Zend\View\HelperPluginManager;
use Prismic;

class InjectPreviewScriptFactory
{

    public function __invoke(ContainerInterface $container) : InjectPreviewScript
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }

        $config  = $container->get('config');

        return new InjectPreviewScript(
            $container->get(Prismic\Api::class),
            $container->get(HelperPluginManager::class),
            $config['prismic']['toolbarScript'],
            $config['prismic']['endpointScript'],
            $config['prismic']['api']['url']
        );
    }

}
