<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\DocumentResolver;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\Service\CurrentDocument;

/**
 * Class DocumentResolverFactory
 *
 * @package ExpressivePrismic\Middleware\Factory
 */
class DocumentResolverFactory
{

    public function __invoke(ContainerInterface $container) : DocumentResolver
    {
        $api             = $container->get(Prismic\Api::class);
        $params          = $container->get(RouteParams::class);
        $currentDocument = $container->get(CurrentDocument::class);

        return new DocumentResolver($api, $params, $currentDocument);
    }
}
