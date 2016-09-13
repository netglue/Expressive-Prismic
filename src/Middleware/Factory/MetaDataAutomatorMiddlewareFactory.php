<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Middleware\MetaDataAutomatorMiddleware;
use ExpressivePrismic\Service\MetaDataAutomator;
use Zend\View\HelperPluginManager;

class MetaDataAutomatorMiddlewareFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : MetaDataAutomatorMiddleware
    {
        if (!$container->has(MetaDataAutomator::class)) {
            throw new \RuntimeException(sprintf(
                'The %s cannot be located in the container',
                MetaDataAutomator::class
            ));
        }
        $automator = $container->get(MetaDataAutomator::class);
        return new MetaDataAutomatorMiddleware($automator);
    }

}
