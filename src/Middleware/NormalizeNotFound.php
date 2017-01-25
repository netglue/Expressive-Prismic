<?php

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\PageNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\RouteResult;

/**
 * Middleware to normalise Not found errors to exceptions
 *
 * @package ExpressivePrismic\Middleware
 */
class NormalizeNotFound
{

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|null $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        /**
         * It's relatively pointless to inspect the response status code
         * because this normalizer is executed before any error handlers,
         * therefore status will likely be 200 regardless of whether all middleware
         * is exhausted, i.e a 404 or if an exception has been thrown, parse error etc.
         *
         * So, we'll figure out if a route has been matched or not to determine whether
         * to throw the 404 Exception
         */

        $hasRoute = $request->getAttribute(RouteResult::class) !== null;
        if (!$hasRoute) {
            PageNotFoundException::throw404();
        }

        return $next($request, $response);
    }

}
