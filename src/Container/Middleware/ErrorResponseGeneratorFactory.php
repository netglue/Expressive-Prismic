<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;

class ErrorResponseGeneratorFactory
{

    public function __invoke(ContainerInterface $container) : ErrorResponseGenerator
    {
        return new ErrorResponseGenerator(
            $container->get(ErrorHandlerPipe::class)
        );
    }
}
