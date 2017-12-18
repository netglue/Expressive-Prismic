<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper\Factory;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\View\Helper\Url;

class UrlFactory
{

    public function __invoke(ContainerInterface $container) : Url
    {
        return new Url(
            $container->get(Prismic\Api::class),
            $container->get(Prismic\LinkResolver::class)
        );
    }

}
