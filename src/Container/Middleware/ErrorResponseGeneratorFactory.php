<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;
use ExpressivePrismic\Middleware\NotFoundPipe;
use Psr\Container\ContainerInterface;

class ErrorResponseGeneratorFactory
{

    public function __invoke(ContainerInterface $container) : ErrorResponseGenerator
    {
        return new ErrorResponseGenerator(
            $container->get(ErrorHandlerPipe::class),
            $container->get(NotFoundPipe::class)
        );
    }
}
