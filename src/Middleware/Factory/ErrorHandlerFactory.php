<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ErrorHandler;
use ExpressivePrismic\Middleware\ErrorHandlerPipe;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class ErrorHandlerFactory
 *
 * @package ExpressivePrismic\Middleware\Factory
 */
class ErrorHandlerFactory
{

    public function __invoke(ContainerInterface $container) : ErrorHandler
    {
        $config          = $container->get('config');
        $options         = $config['prismic']['error_handler'];

        return new ErrorHandler(
            $container->get(ErrorHandlerPipe::class),
            $container->get(Prismic\Api::class),
            $container->get(TemplateRendererInterface::class),
            $container->get(CurrentDocument::class),
            $options['bookmark_404'],
            $options['bookmark_error'],
            $options['template_404'],
            $options['template_error'],
            $options['layout_fallback'],
            $options['template_fallback']
        );
    }

}
