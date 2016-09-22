<?php

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\PageNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        if (
            $response->getStatusCode() === 200
            &&
            $response->getBody()->getSize() === 0
        ) {
            PageNotFoundException::throw404();
        }

        if ($next) {
            return $next($request, $response);
        }

        return $response;
    }

}
