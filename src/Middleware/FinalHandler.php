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
class FinalHandler
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

    /**
     * Name of an error template to use as a fallback when pretty error pages
     * cannot be rendered
     *
     * @var string
     */
    private $templateFallback;

    /**
     * Name of an error layout template to use as a fallback when pretty error pages
     * cannot be rendered
     *
     * @var string
     */
    private $layoutFallback;

    /**
     * Document bookmark for 404 errors
     *
     * @var string
     */
    private $bookmark404;

    /**
     * Document bookmark for server errors
     *
     * @var string
     */
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
        string $templateError = 'error::error',
        string $templateFallback = 'error::fallback',
        string $layoutFallback = 'layout::error-fallback'
    ) {

        $this->api              = $api;
        $this->renderer         = $renderer;
        $this->template404      = $template404;
        $this->templateError    = $templateError;
        $this->bookmark404      = $bookmark404;
        $this->bookmarkError    = $bookmarkError;
        $this->documentRegistry = $documentRegistry;
        $this->templateFallback = $templateFallback;
        $this->layoutFallback = $layoutFallback;
    }

    /**
     * The signature for Final error handler is ($request, $response, $err = null)
     *
     * @param  mixed         $error
     * @param  Request       $request
     * @param  Response      $response
     * @param  null|callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $error = null) : Response
    {
        try {
            if ($error && $error instanceof PageNotFoundException) {
                return $this->render404($request, $response);
            }

            return $this->render500($request, $response, $error);
        } catch(\Exception $e) {
            return $this->renderFallback($request, $response, $error);
        }
    }

    /**
     * Render an error or exception to a template using a bookmarked Prismic document
     *
     * @param  mixed    $error
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    private function render500(Request $request, Response $response, $error = null)
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

    private function renderFallback(Request $request, Response $response, $error = null)
    {
        $response->getBody()->write(
            $this->renderer->render($this->templateFallback, ['layout' => $this->layoutFallback])
        );
        return $response->withStatus(500);
    }

}
