<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\PreviewInitiator;

class PreviewInitiatorFactory
{

    public function __invoke(ContainerInterface $container) : PreviewInitiator
    {
        $api      = $container->get(Prismic\Api::class);
        $resolver = $container->get(Prismic\LinkResolver::class);
        return new PreviewInitiator($api, $resolver);
    }
}
