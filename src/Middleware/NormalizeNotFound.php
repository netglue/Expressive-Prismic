<?php

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\PageNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\RouteResult;


class NormalizeNotFound
{

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (
            $response->getStatusCode() === 200
            &&
            $response->getBody()->getSize() === 0
            &&
            !$routeResult
        ) {
            PageNotFoundException::throw404();
        }

        if ($next) {
            return $next($request, $response);
        }

        return $response;
    }

}
