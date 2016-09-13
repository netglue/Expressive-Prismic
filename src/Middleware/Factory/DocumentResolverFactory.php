<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\DocumentResolver;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\Service\CurrentDocument;

class DocumentResolverFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : DocumentResolver
    {
        $api             = $container->get(Prismic\Api::class);
        $params          = $container->get(RouteParams::class);
        $currentDocument = $container->get(CurrentDocument::class);
        return new DocumentResolver($api, $params, $currentDocument);
    }
}
