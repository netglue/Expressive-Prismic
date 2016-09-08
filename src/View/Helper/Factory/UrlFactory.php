<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\View\Helper\Url;

class UrlFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Url
    {
        $api      = $container->get(Prismic\Api::class);
        $resolver = $container->get(Prismic\LinkResolver::class);
        return new Url($api, $resolver);
    }

}
