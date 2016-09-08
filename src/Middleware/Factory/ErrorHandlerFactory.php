<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;

use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;

class ErrorHandlerFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ErrorHandler
    {
        $api      = $container->get(Prismic\Api::class);
        $config   = $container->get('config');
        $renderer = $container->get(TemplateRendererInterface::class);
        $options  = $config['prismic']['error_handler'];
        return new ErrorHandler(
            $renderer,
            $options['template_404'],
            $options['template_error'],
            null,
            $api,
            $options['bookmark_404'],
            $options['bookmark_error'],
            $options['layout']
        );
    }
}
