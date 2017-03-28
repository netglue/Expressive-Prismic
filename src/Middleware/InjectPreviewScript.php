<?php
/**
 * This file is part of the Expressive Prismic Package
 * Copyright 2016 Net Glue Ltd (https://netglue.uk).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\View\HelperPluginManager;

/**
 * Class InjectPreviewScript
 *
 * @package ExpressivePrismic\Middleware
 */
class InjectPreviewScript implements MiddlewareInterface
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
    private $endpointScript;

    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * InjectPreviewScript constructor.
     *
     * @param HelperPluginManager $helpers
     * @param string              $toolbarScript
     * @param string              $endpointScript
     * @param string              $apiEndpoint
     */
    public function __construct(
        HelperPluginManager $helpers,
        string $toolbarScript,
        string $endpointScript,
        string $apiEndpoint
    ) {
        $this->helpers = $helpers;
        $this->toolbarScript = $toolbarScript;
        $this->endpointScript = $endpointScript;
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * @param  Request           $request
     * @param  DelegateInterface $delegate
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
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
            str_replace(['.', ' '], '_', Prismic\Api::PREVIEW_COOKIE)     => '',
            Prismic\Api::PREVIEW_COOKIE                                   => '',
        ];
        $value = current(array_intersect_key($request->getCookieParams(), $cookieNames));
        if (!empty($value)) {
            $helper = $this->helpers->get('inlineScript');
            $helper->appendScript(sprintf($this->endpointScript, $this->apiEndpoint));
            $helper->appendFile($this->toolbarScript);
        }

        return $delegate->process($request);
    }
}
