<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\ErrorDocumentSetup;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception;

class ErrorDocumentSetupFactory
{

    public function __invoke(ContainerInterface $container) : ErrorDocumentSetup
    {
        $config = $container->get('config');
        if (! isset($config['prismic']['error_handler']['template_error'])) {
            throw new Exception\RuntimeException(
                'No template for the error document has been provided in the key '
                . '[prismic][error_handler][template_error]'
            );
        }
        if (! isset($config['prismic']['error_handler']['bookmark_error'])) {
            throw new Exception\RuntimeException(
                'No API bookmark for the error document has been provided in the key '
                . '[prismic][error_handler][bookmark_error]'
            );
        }

        return new ErrorDocumentSetup(
            $container->get(Prismic\Api::class),
            $container->get(CurrentDocument::class),
            $config['prismic']['error_handler']['bookmark_error'],
            $config['prismic']['error_handler']['template_error']
        );
    }
}
