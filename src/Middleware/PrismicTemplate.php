<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\DocumentNotFoundException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic;

/**
 * Template Renderer for CMS Documents
 *
 * This middleware is implemented as middleware as opposed to a request handler so that when a document
 * cannot be located in the CMS API, we'll fall through to whatever Not Found Handler is configured
 */
class PrismicTemplate implements MiddlewareInterface
{

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        $template = $request->getAttribute('template');
        $document = $request->getAttribute(Prismic\DocumentInterface::class);

        if (! $document || ! $template) {
            DocumentNotFoundException::throw404();
        }

        $view = [
            'document' => $document
        ];

        return new HtmlResponse($this->renderer->render($template, $view));
    }
}
