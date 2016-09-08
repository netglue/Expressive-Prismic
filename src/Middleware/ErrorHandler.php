<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Prismic;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\Utils;
class ErrorHandler
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * Template renderer to use when rendering error pages; if not provided,
     * only the status will be updated.
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

    private $templateFallback;

    private $layout;


    public function __construct(
        TemplateRendererInterface $renderer,
        $template404 = 'error::404',
        $templateError = 'error::error',
        Response $originalResponse = null,
        Prismic\Api $api,
        $bookmark404,
        $bookmarkError,
        $templateFallback,
        $layout = null

    ) {

        $this->api              = $api;
        $this->renderer         = $renderer;
        $this->template404      = $template404;
        $this->templateError    = $templateError;
        $this->bookmark404      = $bookmark404;
        $this->bookmarkError    = $bookmarkError;
        $this->templateFallback = $templateFallback;
        $this->layout           = $layout;
    }

    /**
     * Final handler for an application.
     *
     * @param Request $request
     * @param Response $response
     * @param null|mixed $err
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $err = null)
    {
        if (! $err) {
            return $this->handlePotentialSuccess($request, $response);
        }

        return $this->handleErrorResponse($err, $request, $response);
    }

    /**
     * Handle a non-error condition.
     *
     * Non-error conditions mean either all middleware called $next(), and we
     * have a complete response, or no middleware was able to handle the
     * request.
     *
     * This method determines which occurred, returning the response in the
     * first instance, and returning a 404 response in the second.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function handlePotentialSuccess(Request $request, Response $response)
    {
        if (! $this->originalResponse) {
            // No original response detected; decide whether we have a
            // response to return
            return $this->marshalReceivedResponse($request, $response);
        }

        $originalResponse  = $this->originalResponse;
        $decoratedResponse = $response instanceof StratigilityResponse
            ? $response->getOriginalResponse()
            : $response;

        if ($originalResponse !== $response
            && $originalResponse !== $decoratedResponse
        ) {
            // Response does not match either the original response or the
            // decorated response; return it verbatim.
            return $response;
        }

        if (($originalResponse === $response || $decoratedResponse === $response)
            && $this->bodySize !== $response->getBody()->getSize()
        ) {
            // Response matches either the original response or the
            // decorated response; but the body size has changed; return it
            // verbatim.
            return $response;
        }

        return $this->create404($request, $response);
    }

    /**
     * Determine whether to return the given response, or a 404.
     *
     * If no original response was present, we check to see if we have a 200
     * response with empty content; if so, we treat it as a 404.
     *
     * Otherwise, we return the response intact.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function marshalReceivedResponse(Request $request, Response $response)
    {
        if ($response->getStatusCode() === 200
            && $response->getBody()->getSize() === 0
        ) {
            return $this->create404($request, $response);
        }

        return $response;
    }

    /**
     * Create a 404 response with a bookmarked Prismic document
     *
     * If it's not possible to render the 404 document, the fallback response
     * is returned
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function create404(Request $request, Response $response)
    {
        try {
            $id = $this->api->bookmark($this->bookmark404);
            $document = $this->api->getByID($id);
            $view = [
                'uri' => $request->getUri()
                'document' => $document,
            ];
            // Set the layout if an alternate has been provided
            if ($this->layout) {
                $view['layout'] = $this->layout;
            }
            $response->getBody()->write(
                $this->renderer->render($this->template404, $view)
            );

            return $response->withStatus(404);
        } catch (\Exception $e) {
            return $this->createFallbackError($request, $response, $e);
        }
    }

    /**
     * Render a fallback error template
     *
     * In the event the API is unreachable and attempt to generate either a
     * 404, or error response has resulted in an exception, we'll render
     * a simpler error document.
     *
     * Layout is set to null in case of Zend View
     *
     */
    private function createFallbackError(Request $request, Response $response, $error = null)
    {
        $response = $response->withStatus(Utils::getStatusCode($error, $response));
        $view = [
            'uri' => $request->getUri()
            'layout' => null,
        ];
        $response->getBody()->write(
            $this->renderer->render($this->templateFallback, $view)
        );

        return $response;
    }

    /**
     * Handle an error response.
     *
     * Marshals the response status from the error.
     *
     * If the error is not an exception, it then proxies to handleError();
     * otherwise, it proxies to handleException().
     *
     * @param mixed $error
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function handleErrorResponse($error, Request $request, Response $response)
    {
        $response = $response->withStatus(Utils::getStatusCode($error, $response));

        if (! $error instanceof \Exception && ! $error instanceof \Throwable) {
            return $this->handleError($error, $request, $response);
        }


        return $this->handleException($error, $request, $response);
    }

}
