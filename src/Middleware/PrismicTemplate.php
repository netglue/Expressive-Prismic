<?php

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic;

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
    public function process(Request $request, DelegateInterface $delegate)
    {
        $template = $request->getAttribute('template');
        $document = $request->getAttribute(Prismic\Document::class);

        if (! $document || ! $template) {
            return $delegate->process($request);
        }

        $view = [
            'document' => $document,
            'linkResolver' => $this->linkResolver,
        ];

        return new HtmlResponse($this->renderer->render($template, $view));
    }
}
