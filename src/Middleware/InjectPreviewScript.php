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
     * @var Prismic\Api
     */
    private $api;

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

    public function __construct(
        Prismic\Api $api,
        HelperPluginManager $helpers,
        string $toolbarScript,
        string $endpointScript,
        string $apiEndpoint
    ) {
        $this->api = $api;
        $this->helpers = $helpers;
        $this->toolbarScript = $toolbarScript;
        $this->endpointScript = $endpointScript;
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        if ($this->api->inPreview()) {
            $helper = $this->helpers->get('inlineScript');
            $helper->appendScript(sprintf($this->endpointScript, $this->apiEndpoint));
            $helper->appendFile($this->toolbarScript);
        }

        return $delegate->process($request);
    }
}
