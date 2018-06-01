<?php
declare(strict_types=1);
/**
 * This file is part of the Expressive Prismic Package
 * Copyright 2016 Net Glue Ltd (https://netglue.uk).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace ExpressivePrismic\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
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

    /**
     * Whether to inject the toolbar script all of the time
     *
     * This is useful if you want the 'Edit Button' functionality when not in preview mode
     * @var bool
     */
    private $alwaysInject = false;

    public function __construct(
        Prismic\Api $api,
        HelperPluginManager $helpers,
        string $toolbarScript,
        string $endpointScript,
        string $apiEndpoint,
        bool $alwaysInject = false
    ) {
        $this->api = $api;
        $this->helpers = $helpers;
        $this->toolbarScript = $toolbarScript;
        $this->endpointScript = $endpointScript;
        $this->apiEndpoint = $apiEndpoint;
        $this->alwaysInject = $alwaysInject;
    }

    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        if ($this->api->inPreview() || true === $this->alwaysInject) {
            $helper = $this->helpers->get('inlineScript');
            $helper->appendScript(sprintf($this->endpointScript, $this->apiEndpoint));
            $helper->appendFile($this->toolbarScript);
        }

        return $delegate->handle($request);
    }
}
