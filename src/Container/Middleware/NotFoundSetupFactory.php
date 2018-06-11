<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Prismic;
use ExpressivePrismic\Middleware\NotFoundSetup;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception;

class NotFoundSetupFactory
{

    public function __invoke(ContainerInterface $container) : NotFoundSetup
    {
        $config = $container->get('config');
        if (! isset($config['prismic']['error_handler']['template_404'])) {
            throw new Exception\RuntimeException(
                'No template for the 404 error has been provided in the key '
                . '[prismic][error_handler][template_404]'
            );
        }
        if (! isset($config['prismic']['error_handler']['bookmark_404'])) {
            throw new Exception\RuntimeException(
                'No API bookmark for the 404 error has been provided in the key '
                . '[prismic][error_handler][bookmark_404]'
            );
        }

        return new NotFoundSetup(
            $container->get(Prismic\Api::class),
            $container->get(CurrentDocument::class),
            $config['prismic']['error_handler']['bookmark_404'],
            $config['prismic']['error_handler']['template_404']
        );
    }
}
