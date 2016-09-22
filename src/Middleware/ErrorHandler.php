<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\Utils;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception\PageNotFoundException;
use Throwable;

/**
 * Class ErrorHandler
 *
 * @package ExpressivePrismic\Middleware
 */
class ErrorHandler
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var CurrentDocument
     */
    private $documentRegistry;

    /**
     * Template renderer to use when rendering error pages
     *
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * Name of 404 template to use when creating 404 response content with the
     * template renderer.
     *
     * @var string
     */
    private $template404;

    /**
     * Name of error template to use when creating response content for pages
     * with errors.
     *
     * @var string
     */
    private $templateError;

    private $bookmark404;

    private $bookmarkError;


    /**
     * ErrorHandler constructor.
     *
     * @param Prismic\Api               $api
     * @param TemplateRendererInterface $renderer
     * @param CurrentDocument           $documentRegistry
     * @param string                    $bookmark404
     * @param string                    $bookmarkError
     * @param string                    $template404
     * @param string                    $templateError
     */
    public function __construct(
        Prismic\Api $api,
        TemplateRendererInterface $renderer,
        CurrentDocument $documentRegistry,
        string $bookmark404,
        string $bookmarkError,
        string $template404 = 'error::404',
        string $templateError = 'error::error'
    ) {

        $this->api              = $api;
        $this->renderer         = $renderer;
        $this->template404      = $template404;
        $this->templateError    = $templateError;
        $this->bookmark404      = $bookmark404;
        $this->bookmarkError    = $bookmarkError;
        $this->documentRegistry = $documentRegistry;
    }

    /**
     * The signature for error middleware is ($error, $request, $response, $next)
     *
     * @param  mixed         $error
     * @param  Request       $request
     * @param  Response      $response
     * @param  null|callable $next
     * @return Response
     */
    public function __invoke(Throwable $error, Request $request, Response $response, callable $next = null) : Response
    {
        if ($error && $error instanceof PageNotFoundException) {
            return $this->render404($request, $response);
        }

        if ($error && $error instanceof Throwable) {
            return $this->render500($error, $request, $response);
        }

        if ($next) {
            return $next($request, $response);
        }

        return $response;
    }

    /**
     * Render an error or exception to a template using a bookmarked Prismic document
     *
     * @param  mixed    $error
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    protected function render500(Throwable $error, Request $request, Response $response)
    {
        $id = $this->api->bookmark($this->bookmarkError);
        $document = $this->api->getByID($id);
        if (!$document) {
            throw new \RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved', 500, $error);
        }
        $this->documentRegistry->setDocument($document);
        $request = $request->withAttribute(Prismic\Document::class, $document);
        $view = [
            'uri' => $request->getUri(),
            'document' => $document,
            'error' => $error,
        ];
        $response->getBody()->write(
            $this->renderer->render($this->templateError, $view)
        );

        return $response->withStatus(500);
    }

    /**
     * Create a 404 response with a bookmarked Prismic document
     *
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    private function render404(Request $request, Response $response)
    {
        $id = $this->api->bookmark($this->bookmark404);
        $document = $this->api->getByID($id);
        if (!$document) {
            throw new \RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved');
        }
        $this->documentRegistry->setDocument($document);
        $request = $request->withAttribute(Prismic\Document::class, $document);
        $view = [
            'uri' => $request->getUri(),
            'document' => $document,
        ];
        $response->getBody()->write(
            $this->renderer->render($this->template404, $view)
        );

        return $response->withStatus(404);
    }

}
