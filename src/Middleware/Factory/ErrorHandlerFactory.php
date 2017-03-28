<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ErrorHandler;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;
use ExpressivePrismic\Middleware;

use Zend\Stratigility\MiddlewarePipe;

/**
 * Class ErrorHandlerFactory
 *
 * @package ExpressivePrismic\Middleware\Factory
 */
class ErrorHandlerFactory
{

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return ErrorHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ErrorHandler
    {
        $api             = $container->get(Prismic\Api::class);
        $config          = $container->get('config');
        $renderer        = $container->get(TemplateRendererInterface::class);
        $options         = $config['prismic']['error_handler'];
        $currentDocument = $container->get(CurrentDocument::class);

        $middleware = $config['prismic']['error_handler']['middleware'];

        $pipe = new MiddlewarePipe;
        foreach ($middleware as $name) {
            $pipe->pipe($container->get($name));
        }

        return new ErrorHandler(
            $pipe,
            $api,
            $renderer,
            $currentDocument,
            $options['bookmark_404'],
            $options['bookmark_error'],
            $options['template_404'],
            $options['template_error'],
            $options['layout_fallback'],
            $options['template_fallback']
        );
    }
}
