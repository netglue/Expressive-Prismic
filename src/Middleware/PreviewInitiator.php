<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Http\Header\SetCookie;
use Zend\Diactoros\Response\RedirectResponse;

class PreviewInitiator
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var Prismic\LinkResolver
     */
    private $linkResolver;

    public function __construct(Prismic\Api $api, Prismic\LinkResolver $linkResolver)
    {
        $this->api = $api;
        $this->linkResolver = $linkResolver;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        $query = $request->getQueryParams();
        if (!isset($query['token']) || empty($query['token'])) {
            return $response->withStatus(400);
        }

        $token = urldecode($query['token']);

        /**
         * If you don't set the cookie, the Prismic Preview Icon will not show up
         * at the bottom of the page
         */
        $expires = time() + (29 * 60);
        $cookie = new SetCookie(Prismic\Api::PREVIEW_COOKIE, $token, $expires);

        /**
         * Figure out URL and redirect
         */
        $url = $this->api->previewSession($token, $this->linkResolver, '/');

        return new RedirectResponse($url, 302, [$cookie->getFieldName() => $cookie->getFieldValue()]);
    }
}
