<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception;

class ErrorResponseGeneratorFactory
{

    public function __invoke(ContainerInterface $container) : ErrorResponseGenerator
    {
        $config = $container->get('config');
        if (! isset($config['prismic']['error_handler']['template_error'])) {
            throw new Exception\RuntimeException(
                'No template for server errors has been provided in the key '
                . '[prismic][error_handler][template_error]'
            );
        }
        if (! isset($config['prismic']['error_handler']['bookmark_error'])) {
            throw new Exception\RuntimeException(
                'No API bookmark for server errors has been provided in the key '
                . '[prismic][error_handler][bookmark_error]'
            );
        }
        return new ErrorResponseGenerator(
            $container->get('ExpressivePrismic\Middleware\ErrorHandlerPipe'),
            $container->get(Prismic\Api::class),
            $container->get(CurrentDocument::class),
            $config['prismic']['error_handler']['bookmark_error'],
            $config['prismic']['error_handler']['template_error']
        );
    }
}
