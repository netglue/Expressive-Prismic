<?php

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Http\Header\SetCookie;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\View\HelperPluginManager;

class InjectPreviewScript
{

    /**
     * @var HelperPluginManager
     */
    private $helpers;

    /**
     * @var string
     */
    private $toolbarScript;

    /**
     * @var string
     */
    private $editScript;

    /**
     * @var string
     */
    private $apiEndpoint;

    public function __construct(HelperPluginManager $helpers, string $toolbarScript, string $editScript, string $apiEndpoint)
    {
        $this->helpers       = $helpers;
        $this->toolbarScript = $toolbarScript;
        $this->editScript    = $editScript;
        $this->apiEndpoint   = $apiEndpoint;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        /**
         * Check for the existence of the Preview Cookie
         *
         * Note. The Prismic cookies, generally contain dots. PHP replaces dots
         * and spaces with the underscore, so we need to do the same to
         * match the cookie name on the incoming request, but, it depends whether
         * the cookie data is extracted from $_COOKIE, or $_SERVER['HTTP_COOKIE']
         * so we need to check both name variants. Shit.
         */
        $cookieNames = [
            str_replace(['.',' '], '_', Prismic\Api::PREVIEW_COOKIE) => '',
            Prismic\Api::PREVIEW_COOKIE => '',
        ];
        $value = current(array_intersect_key($request->getCookieParams(), $cookieNames));
        if (!empty($value)) {
            $helper = $this->helpers->get('inlineScript');
            $helper->appendScript(sprintf($this->editScript, $this->apiEndpoint));
            $helper->appendFile($this->toolbarScript);
        }

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }
}
