<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Prismic\DocumentInterface;
use Zend\Expressive\Router\RouteResult;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\Exception;

/**
 * DocumentResolver Middleware.
 *
 * Tries to resolve the current CMS document based on the matched route/request
 *
 * @package ExpressivePrismic\Middleware
 */
class DocumentResolver implements MiddlewareInterface
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var RouteParams
     */
    private $routeParams;

    /**
     * @var CurrentDocument
     */
    private $documentRegistry;

    public function __construct(Prismic\Api $api, RouteParams $params, CurrentDocument $documentRegistry)
    {
        $this->api              = $api;
        $this->routeParams      = $params;
        $this->documentRegistry = $documentRegistry;
    }

    public function process(Request $request, RequestHandlerInterface $delegate) : Response
    {
        // Get hold of the matched route (RouteResult) so we can inspect and resolve a document
        $routeResult = $request->getAttribute(RouteResult::class);

        if (! $routeResult) {
            throw new Exception\RuntimeException(
                'No route has yet been matched so it\'s not possible to resolve a document'
            );
        }

        /**
         * Possible Ways to Match
         *
         * - A bookmark ultimately resolves to a document ID or null, so there can be only 1 result
         * - A document ID is unique to a single document
         * - A document UID is only unique to documents of the same type, so both must be available to match
         */

        $document = $this->resolveWithBookmark($routeResult);

        if (! $document) {
            $document = $this->resolveWithUid($routeResult);
        }

        if (! $document) {
            $document = $this->resolveWithId($routeResult);
        }

        if ($document) {
            $this->documentRegistry->setDocument($document);
            $request = $request->withAttribute(DocumentInterface::class, $document);
        }

        return $delegate->handle($request);
    }

    private function resolveWithBookmark(RouteResult $routeResult) :? DocumentInterface
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getBookmark();
        $bookmark = isset($params[$search]) && ! empty($params[$search]) ? (string) $params[$search] : null;
        if ($bookmark) {
            $id = $this->api->bookmark($bookmark);
            if ($id) {
                /** @var Document|null */
                return $this->api->getByID($id);
            }
        }

        return null;
    }

    private function resolveWithId(RouteResult $routeResult) :? DocumentInterface
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getId();
        $id = isset($params[$search]) && ! empty($params[$search]) ? (string) $params[$search] : null;
        if ($id) {
            return $this->api->getById($id);
        }

        return null;
    }

    private function resolveWithUid(RouteResult $routeResult) :? DocumentInterface
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getUid();
        $uid    = isset($params[$search]) && ! empty($params[$search]) ? (string) $params[$search] : null;
        $search = $this->routeParams->getType();
        $type   = isset($params[$search]) && ! empty($params[$search]) ? (string) $params[$search] : null;
        if (! $type || ! $uid) {
            return null;
        }
        return $this->api->getByUID($type, $uid);
    }
}
