<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
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
        $document = $this->locateErrorDocument();

        if ($document) {
            $this->documentRegistry->setDocument($document);
            $request = $request->withAttribute(Prismic\Document::class, $document);
            $request = $request->withAttribute('template', $this->template);

            try {
                $response = $this->pipe->process($request, $this);
                $response = $response->withStatus(Utils::getStatusCode($error, $response));
                return $response;
            } catch (\Throwable $e) {
                /**
                 * An exception was thrown render the error page (FFS)
                 */
            }
        }
        /**
         * Return a plain response as there's not much else we can do
         */
        return $this->generateFallbackResponse();
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
    public function process(Request $request)
    {
        return $this->generateFallbackResponse();
    }

    private function locateErrorDocument() :? Prismic\Document
    {
        $id = $this->api->bookmark($this->bookmark);
        if (!$id) {
            return null;
        }
        $document = $this->api->getByID($id);
        if (!$document) {
            throw new \RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved');
        }
        return $document;
    }

    private function generateFallbackResponse() : Response
    {
        return new TextResponse('An Unexpected Error Occurred', 500);
    }

}
