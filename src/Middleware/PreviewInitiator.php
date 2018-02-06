<?php

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Http\Header\SetCookie;
use Zend\Diactoros\Response\RedirectResponse;

class PreviewInitiator implements MiddlewareInterface
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

    /**
     * @param  Request           $request
     * @param  DelegateInterface $delegate
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        $query = $request->getQueryParams();
        if (!isset($query['token']) || empty($query['token'])) {
            // Pass through in order to raise a 404
            return $delegate->process($request);
        }

        $token = urldecode($query['token']);

        /**
         * If you don't set the cookie, the Prismic Preview Icon will not show up
         * at the bottom of the page
         */

        /**
         * @todo Ideally cookie expiry would be configurable
         */
        $expires = time() + (29 * 60);

        /** @var \Psr\Http\Message\UriInterface */
        $uri = $request->getUri();

        $cookie = new SetCookie(
            Prismic\Api::PREVIEW_COOKIE,
            $token,
            $expires,
            null, // $path - Can't see a use case for limiting to specific path right now
            $uri->getHost(), // $domain
            ($uri->getScheme() === 'https'), // $secure - true if current scheme is
            false // $httpOnly - Nope, JS needs this cookie
        );

        /**
         * Figure out URL and redirect
         */
        $url = $this->api->previewSession($token, $this->linkResolver, '/');

        return new RedirectResponse($url, 302, [$cookie->getFieldName() => $cookie->getFieldValue()]);
    }
}
