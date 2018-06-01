<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use ExpressivePrismic\Exception;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\Utils;
use Zend\Diactoros\Response\TextResponse;

class ErrorResponseGenerator implements DelegateInterface
{
    /**
     * @var CurrentDocument
     */
    private $documentRegistry;

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var string
     */
    private $bookmark;

    /**
     * @var string
     */
    private $template;

    /**
     * @var MiddlewarePipe
     */
    private $pipe;

    public function __construct(
        MiddlewarePipe $pipe,
        Prismic\Api $api,
        CurrentDocument $documentRegistry,
        string $documentBookmark,
        string $templateName
    ) {
        $this->pipe = $pipe;
        $this->documentRegistry = $documentRegistry;
        $this->api              = $api;
        $this->bookmark         = $documentBookmark;
        $this->template         = $templateName;
    }

    public function __invoke($error, Request $request, Response $response) : Response
    {
        try {
            $document = $this->locateErrorDocument();
            $this->documentRegistry->setDocument($document);
            $request = $request->withAttribute(Prismic\DocumentInterface::class, $document);
            $request = $request->withAttribute('template', $this->template);
            $response = $this->pipe->process($request, $this);
            $response = $response->withStatus(Utils::getStatusCode($error, $response));
            return $response;
        } catch (\Throwable $e) {
            return $this->handle($request);
        }
    }

    /**
     * As the handler is composed with a MiddlewarePipe, we need a delegate in order to
     * call $pipe->process(), that's why we're implementing this method.
     *
     * This method should never be called because if a problem occurs during rendering of the
     * CMS error page (In the Pipe itself), generateFallbackResponse() will be called.
     *
     * Anyhow, in case the pipe is modifed and fails to return a response, we'll call the
     * fallback method here too.
     */
    public function handle(Request $request) : Response
    {
        return $this->generateFallbackResponse();
    }

    /**
     * Locating the error document successfully is not optional.
     * It must succeed or an exception is thrown
     */
    private function locateErrorDocument() : Prismic\DocumentInterface
    {
        $id = $this->api->bookmark($this->bookmark);
        if (! $id) {
            throw new Exception\RuntimeException(
                'Cannot generate CMS driven Error page. '
                . 'Error document bookmark does not reference a valid document ID'
            );
        }
        try {
            $document = $this->api->getById($id);
            $previous = null;
        } catch (Prismic\Exception\ExceptionInterface $exception) {
            $document = null;
            $previous = $exception;
        }
        if (! $document) {
            throw new Exception\RuntimeException(
                'Cannot generate CMS driven Error page. Error document cannot be resolved',
                404,
                $previous
            );
        }
        return $document;
    }

    private function generateFallbackResponse() : Response
    {
        return new TextResponse('An Unexpected Error Occurred', 500);
    }
}
