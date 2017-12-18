<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Exception;
class ErrorHandlerPipeFactory
{

    public function __invoke(ContainerInterface $container) : MiddlewarePipe
    {
        $config = $container->get('config');
        if (!isset($config['prismic']['error_handler']['middleware'])) {
            throw new Exception\RuntimeException('Cannot create an error handler pipeline without middleware provided in config under [prismic][error_handler][middleware]');
        }

        $middleware = $config['prismic']['error_handler']['middleware'];

        $pipe = new MiddlewarePipe;
        foreach ($middleware as $name) {
            $pipe->pipe($container->get($name));
        }

        return $pipe;
    }
}
