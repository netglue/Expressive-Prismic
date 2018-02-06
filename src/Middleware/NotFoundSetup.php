<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

use ExpressivePrismic\Exception;
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;

class NotFoundSetup implements MiddlewareInterface
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
     * @var bool
     */
    private $fallback;

    public function __construct(
        Prismic\Api $api,
        CurrentDocument $documentRegistry,
        string $documentBookmark,
        string $templateName,
        bool $fallback = false
    ) {
        $this->documentRegistry = $documentRegistry;
        $this->api              = $api;
        $this->bookmark         = $documentBookmark;
        $this->template         = $templateName;
        $this->fallback         = $fallback;
    }

    public function process(Request $request, DelegateInterface $delegate)
    {
        $document = $this->locateErrorDocument();
        if ($document) {
            $this->documentRegistry->setDocument($document);
            $request = $request->withAttribute(Prismic\Document::class, $document);
            $request = $request->withAttribute('template', $this->template);
        }

        $response = $delegate->process($request);
        return $response->withStatus(404);
    }

    /**
     * Return the Error Document from the API
     *
     * If we want to fallback to normal 404 rendering, we return null,
     * otherwise an exception is thrown if we cannot retrieve the correct document
     */
    private function locateErrorDocument() :? Prismic\Document
    {
        $id = $this->api->bookmark($this->bookmark);
        if (!$id) {
            if ($this->fallback) {
                return null;
            }
            throw new Exception\RuntimeException('Cannot generate CMS driven Error page. Error document bookmark does not reference a current document ID');
        }
        $document = $this->api->getByID($id);
        if (!$document) {
            if ($this->fallback) {
                return null;
            }
            throw new Exception\RuntimeException('Cannot generate CMS driven Error page. Error document cannot be resolved');
        }
        return $document;
    }

}
