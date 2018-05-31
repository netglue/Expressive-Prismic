<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Middleware;

class NotFoundPipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $config = $container->get('config');

        $defaultPipe = [
            Middleware\InjectPreviewScript::class,
            Middleware\ExperimentInitiator::class,
            Middleware\NotFoundSetup::class,
            Middleware\PrismicTemplate::class,
        ];

        $configuredPipe = null;

        if (isset($config['prismic']['error_handler']['middleware_404'])) {
            $configuredPipe = $config['prismic']['error_handler']['middleware_404'];
        }

        $middleware = $configuredPipe
                    ? $configuredPipe
                    : $defaultPipe;

        if (! is_array($middleware)) {
            throw new Exception\RuntimeException(
                'Cannot create a 404 handler pipeline without middleware provided in config under '
                . '[prismic][error_handler][middleware_404]'
            );
        }

        $pipe = new MiddlewarePipe;
        foreach ($middleware as $name) {
            $pipe->pipe($container->get($name));
        }

        return $pipe;
    }
}
