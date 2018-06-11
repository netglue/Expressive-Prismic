<?php
declare(strict_types=1);

namespace ExpressivePrismic\Handler;

use ExpressivePrismic\Exception\DocumentNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic;

/**
 * Template Renderer for CMS Documents
 *
 * Either renders the resolved document or throws a DocumentNotFoundException
 */
class PrismicTemplate implements RequestHandlerInterface
{

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function handle(Request $request) : Response
    {
        $template = $request->getAttribute('template');
        $document = $request->getAttribute(Prismic\DocumentInterface::class);

        if (! $document) {
            DocumentNotFoundException::throw404();
        }

        $view = [
            'document' => $document
        ];

        return new HtmlResponse($this->renderer->render($template, $view));
    }
}
