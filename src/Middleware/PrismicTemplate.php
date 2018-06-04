<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

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

    /**
     * @var Prismic\LinkResolver
     */
    private $linkResolver;

    /**
     * @param TemplateRendererInterface $renderer     We'll be using this to render templates
     * @param Prismic\LinkResolver      $linkResolver The link resolver is passed to the view as a parameter
     */
    public function __construct(TemplateRendererInterface $renderer, Prismic\LinkResolver $linkResolver)
    {
        $this->renderer = $renderer;
        $this->linkResolver = $linkResolver;
    }

    /**
     * @param  Request           $request
     * @param  DelegateInterface $delegate
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        $template = $request->getAttribute('template');
        $document = $request->getAttribute(Prismic\DocumentInterface::class);

        if (! $document || ! $template) {
            return $delegate->handle($request);
        }

        $view = [
            'document' => $document,
            'linkResolver' => $this->linkResolver,
        ];

        return new HtmlResponse($this->renderer->render($template, $view));
    }
}
