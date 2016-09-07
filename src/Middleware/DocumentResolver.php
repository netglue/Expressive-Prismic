<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Expressive\Router\RouteResult;
use ExpressivePrismic\Service\RouteParams;

class DocumentResolver
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var RouteParams
     */
    private $routeParams;

    public function __construct(Prismic\Api $api, RouteParams $params)
    {
        $this->api = $api;
        $this->routeParams = $params;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        // Get hold of the matched route (RouteResult) so we can inspect and resolve a document
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult) {
            throw new \RuntimeException('No route has yet been matched so it\'s not possible to resolve a document');
        }

        /**
         * Possible Ways to Match
         *
         * - A bookmark ultimately resolves to a document ID or null, so there can be only 1 result
         * - A document ID is unique to a single document
         * - A document UID is only unique to documents of the same type, so both must be available to match
         */

        $document = $this->resolveWithBookmark($routeResult);

        if (!$document) {
            $document = $this->resolveWithUid($routeResult);
        }

        if (!$document) {
            $document = $this->resolveWithId($routeResult);
        }

        if ($document) {
            $request = $request->withAttribute(Prismic\Document::class, $document);
        }

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }

    /**
     * @param RouteResult $routeResult
     * @return Prismic\Document|null
     */
    private function resolveWithBookmark(RouteResult $routeResult)
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getBookmark();
        $bookmark = isset($params[$search]) && !empty($params[$search]) ? $params[$search] : null;
        if ($bookmark) {
            $id = $this->api->bookmark($bookmark);
            if ($id) {
                return $this->api->getByID($id);
            }
        }
    }

    /**
     * @param RouteResult $routeResult
     * @return Prismic\Document|null
     */
    private function resolveWithId(RouteResult $routeResult)
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getId();
        $id = isset($params[$search]) && !empty($params[$search]) ? $params[$search] : null;
        if ($id) {
            return $this->api->getByID($id);
        }
    }

    /**
     * @param RouteResult $routeResult
     * @return Prismic\Document|null
     */
    private function resolveWithUid(RouteResult $routeResult)
    {
        $params = $routeResult->getMatchedParams();
        $search = $this->routeParams->getUid();
        $uid    = isset($params[$search]) && !empty($params[$search]) ? $params[$search] : null;
        $search = $this->routeParams->getType();
        $type   = isset($params[$search]) && !empty($params[$search]) ? $params[$search] : null;
        if (!$type || !$uid) {
            return;
        }
        return $this->api->getByUID($type, $uid);
    }
}
