<?php

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\Utils;
use Zend\Stratigility\MiddlewarePipe;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception\PageNotFoundException;
use Throwable;


/**
 * Class ErrorHandler
 *
 * @package ExpressivePrismic\Middleware
 */
class ErrorHandler implements DelegateInterface
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
     * @var MiddlewarePipe
     */
    private $pipe;

    /**
     * The response is stored here on __invoke so that process() can return it
     * @var Response
     */
    private $response;

    /**
     * ErrorHandler constructor.
     *
     * @param MiddlewarePipe            $pipe
     * @param Prismic\Api               $api
     * @param TemplateRendererInterface $renderer
     * @param CurrentDocument           $documentRegistry
     * @param string                    $bookmark404
     * @param string                    $bookmarkError
     * @param string                    $template404
     * @param string                    $templateError
     * @param string                    $templateFallback
     * @param string                    $layoutFallback
     */
    public function __construct(
        MiddlewarePipe $pipe,
        Prismic\Api $api,
        TemplateRendererInterface $renderer,
        CurrentDocument $documentRegistry,
        string $bookmark404,
        string $bookmarkError,
        string $template404      = 'error::404',
        string $templateError    = 'error::error',
        string $templateFallback = 'error::fallback',
        string $layoutFallback   = 'layout::error-fallback'
    ) {

        $this->pipe             = $pipe;
        $this->api              = $api;
        $this->renderer         = $renderer;
        $this->documentRegistry = $documentRegistry;

        $this->template404      = $template404;
        $this->templateError    = $templateError;
        $this->bookmark404      = $bookmark404;
        $this->bookmarkError    = $bookmarkError;
        $this->templateFallback = $templateFallback;
        $this->layoutFallback   = $layoutFallback;
    }

    /**
     * Error Handler uses the Error Response Generator Signature
     *
     * @param  mixed         $error
     * @param  Request       $request
     * @param  Response      $response
     * @return Response
     */
    public function __invoke($error, Request $request, Response $response) : Response
    {
        $this->response = $response;

        try {
            if ($error && $error instanceof PageNotFoundException) {
                return $this->render404($request, $response);
            }

            return $this->render500($error, $request, $response);
        } catch (Throwable $e) {
            return $this->renderFallback();
        }
    }

    /**
     * As the handler is composed with a MiddlewarePipe, the final delegate is $this,
     * Therefore, we implement DelegateInterface and this method to perform the final render
     *
     * @param Request $request
     * @return Response
     */
    public function process(Request $request)
    {
        $data = $request->getAttribute(__CLASS__);

        $this->response->getBody()->write(
            $this->renderer->render($data['template'], $data)
        );

        return $this->response->withStatus($data['code']);
    }

    /**
     * Render an error or exception to a template using a bookmarked Prismic document
     *
     * @param  mixed    $error
     * @param  Request  $request
     * @param  Response $response
     * @return Response
     */
    private function render500($error = null, Request $request, Response $response)
    {
        /**
         * Locate error document, set some attributes and fire the request down the composed pipeline
         */
        $id = $this->api->bookmark($this->bookmarkError);
        $document = $this->api->getByID($id);
        if (!$document) {
            throw new \RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved', 500, $error);
        }
        $this->documentRegistry->setDocument($document);
        $request = $request->withAttribute(Prismic\Document::class, $document);
        $request = $request->withAttribute(__CLASS__, [
            'template' => $this->templateError,
            'code' => 500,
            'error' => $error,
            'document' => $document,
            'uri' => $request->getUri(),
        ]);
        // Ultimately, this ends up at $this->process unless anything returns a response first
        return $this->pipe->process($request, $this);
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
        /**
         * Locate error document, set some attributes and fire the request down the composed pipeline
         */
        $id = $this->api->bookmark($this->bookmark404);
        $document = $this->api->getByID($id);
        if (!$document) {
            throw new \RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved');
        }
        $this->documentRegistry->setDocument($document);
        $request = $request->withAttribute(Prismic\Document::class, $document);
        $request = $request->withAttribute(__CLASS__, [
            'template' => $this->template404,
            'code' => 404,
            'error' => null,
            'document' => $document,
            'uri' => $request->getUri(),
        ]);
        return $this->pipe->process($request, $this);
    }

    /**
     * Return a response when an error occurs rendering the CMS driven error pages
     * @param  Response $response
     * @return Response
     */
    private function renderFallback() : Response
    {
        $this->response->getBody()->write(
            $this->renderer->render($this->templateFallback, ['layout' => $this->layoutFallback])
        );
        return $this->response->withStatus(500);
    }

}
