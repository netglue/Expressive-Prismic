<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

use ExpressivePrismic\Exception;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Prismic\Exception\ExceptionInterface as CMSException;

class ErrorDocumentSetup implements MiddlewareInterface
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

    public function __construct(
        Prismic\Api $api,
        CurrentDocument $documentRegistry,
        string $documentBookmark,
        string $templateName
    ) {
        $this->documentRegistry = $documentRegistry;
        $this->api              = $api;
        $this->bookmark         = $documentBookmark;
        $this->template         = $templateName;
    }

    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        $document = $this->locateErrorDocument();
        if ($document) {
            $this->documentRegistry->setDocument($document);
            $request = $request->withAttribute(Prismic\DocumentInterface::class, $document);
            $request = $request->withAttribute('template', $this->template);
        }

        $response = $delegate->handle($request);
        return $response->withStatus(404, '');
    }

    /**
     * Return the Error Document from the API
     *
     * An exception is thrown if the correct document cannot be retrieved
     */
    private function locateErrorDocument() : Prismic\DocumentInterface
    {
        $id = $this->api->bookmark($this->bookmark);
        if (! $id) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot generate CMS driven Error page. '
                . 'The error document bookmark "%s" does not reference a current document ID',
                $this->bookmark
            ));
        }
        try {
            $document = $this->api->getById($id);
        } catch (CMSException $exception) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot generate CMS driven Error page. '
                . 'An exception occurred retrieving the error document with the id "%s"',
                $id
            ), 0, $exception);
        }
        if (! $document) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot generate CMS driven Error page. '
                . 'The error document bookmark "%s" resolved to the id "%s" but the document cannot be found',
                $this->bookmark,
                $id
            ));
        }
        return $document;
    }
}
