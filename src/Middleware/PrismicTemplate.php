<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic;

class PrismicTemplate
{

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    private $helpers;

    private $linkResolver;

    public function __construct(TemplateRendererInterface $renderer, $helperManager, $linkResolver)
    {
        $this->renderer = $renderer;
        $this->helpers = $helperManager;
        $this->linkResolver = $linkResolver;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        $template = $request->getAttribute('template');
        $document = $request->getAttribute(Prismic\Document::class);

        $view = [
            'document' => $document,
        ];

        return new HtmlResponse($this->renderer->render($template, $view));
    }

}
