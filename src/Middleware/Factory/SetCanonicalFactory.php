<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Middleware\SetCanonical;
use Zend\View\HelperPluginManager;
use Prismic\LinkResolver;

class SetCanonicalFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SetCanonical
    {
        if (!$container->has(HelperPluginManager::class)) {
            throw new \RuntimeException('The Zend\View\HelperPluginManager cannot be located in the container');
        }
        $helpers    = $container->get(HelperPluginManager::class);
        $resolver   = $container->get(LinkResolver::class);

        return new SetCanonical($resolver, $helpers);
    }

}
