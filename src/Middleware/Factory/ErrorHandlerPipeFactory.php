<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Exception;
use ExpressivePrismic\Middleware;

class ErrorHandlerPipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $config = $container->get('config');

        $defaultPipe = [
            Middleware\InjectPreviewScript::class,
            Middleware\ExperimentInitiator::class,
            Middleware\PrismicTemplate::class,
        ];

        $configuredPipe = null;

        if (isset($config['prismic']['error_handler']['middleware_error'])) {
            $configuredPipe = $config['prismic']['error_handler']['middleware_error'];
        }

        $middleware = $configuredPipe
                    ? $configuredPipe
                    : $defaultPipe;

        if (!is_array($middleware)) {
            throw new Exception\RuntimeException('Cannot create an error handler pipeline without middleware provided in config under [prismic][error_handler][middleware_error]');
        }

        $pipe = new MiddlewarePipe;
        foreach ($middleware as $name) {
            $pipe->pipe($container->get($name));
        }

        return $pipe;
    }
}
