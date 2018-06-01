<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\View\Helper;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\View\Helper\Url;

class UrlFactory
{

    public function __invoke(ContainerInterface $container) : Url
    {
        return new Url(
            $container->get(Prismic\Api::class)
        );
    }
}
