<?php
declare(strict_types=1);

/**
 * If there are potentially multiple routes that can display your content,
 * this middleware will use the link resolver to set a canonical link
 * for the requested document.
 */

namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\View\HelperPluginManager;
use Prismic;
use Zend\Expressive\Helper\ServerUrlHelper;

class SetCanonical
{

    /**
     * @var Prismic\LinkResolver
     */
    private $linkResolver;

    /**
     * @var HelperPluginManager
     */
    private $helpers;

    /**
     * @var ServerUrlHelper
     */
    private $serverUrl;

    public function __construct(Prismic\LinkResolver $resolver, HelperPluginManager $helpers, ServerUrlHelper $serverUrl)
    {
        $this->linkResolver = $resolver;
        $this->helpers = $helpers;
        $this->serverUrl = $serverUrl;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        if ($document = $request->getAttribute(Prismic\Document::class)) {
            $canonical = $this->serverUrl->generate($this->linkResolver->resolveDocument($document));

            $helper = $this->helpers->get('headLink');
            $helper([
                'rel' => 'canonical',
                'href' => $canonical,
            ]);
            $doctype = $this->helpers->get('doctype');
            $doctype($doctype::HTML5);
            $meta = $this->helpers->get('headMeta');
            $meta->setProperty('og:url', $canonical);
            $meta->setName('twitter:url', $canonical);
            $meta->setItemprop('url', $canonical);
        }

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }

}
