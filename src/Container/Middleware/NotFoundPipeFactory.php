<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Expressive\MiddlewareFactory;
use ExpressivePrismic\Middleware;

class NotFoundPipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $factory = $container->get(MiddlewareFactory::class);

        $pipeline = new MiddlewarePipe;
        $pipeline->pipe($factory->prepare(Middleware\InjectPreviewScript::class));
        $pipeline->pipe($factory->prepare(Middleware\ExperimentInitiator::class));
        $pipeline->pipe($factory->prepare(Middleware\NotFoundSetup::class));
        $pipeline->pipe($factory->prepare(Middleware\PrismicTemplate::class));

        return $pipeline;
    }
}
