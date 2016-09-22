<?php
declare(strict_types=1);
namespace ExpressivePrismic;

use Prismic;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\ExceptionInterface as RouterException;
use ExpressivePrismic\Service\RouteParams;

class LinkResolver extends Prismic\LinkResolver
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var RouteParams
     */
    private $routeParams;

    /**
     * @var array
     */
    private $routeConfig;

    /**
     * @var UrlHelper
     */
    private $urlHelper;



    public function __construct(Prismic\Api $api, RouteParams $params, array $routeConfig, UrlHelper $urlHelper)
    {
        $this->api = $api;
        $this->routeParams = $params;
        $this->routeConfig = $routeConfig;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param LinkInterface $link
     * @return string|null
     */
    public function resolve($link)
    {
        if (!$link instanceof LinkInterface) {
            return null;
        }

        if ($link instanceof DocumentLink) {

            // Is the link broken?
            if ($link->isBroken()) {
                return null;
            }

            if ($url = $this->tryResolveAsBookmark($link)) {
                return $url;
            }

            if ($url = $this->tryResolveAsType($link)) {
                return $url;
            }

            if ($url = $this->tryResolveAsId($link)) {
                return $url;
            }

            return null;
        }

        return $link->getUrl($this);
    }

    /**
     * The most generic route would reference only the id
     *
     * @param DocumentLink $link
     * @return string|null
     */
    protected function tryResolveAsId(DocumentLink $link)
    {
        $routes = array_filter($this->routeConfig, function($route) {
            $rp = $this->routeParams;
            if (!isset($route['options']['defaults'])) {
                return false;
            }
            if (!array_key_exists($rp->getId(), $route['options']['defaults'])) {
                return false;
            }
            // If ID & Type are available, it means the route previously didn't match
            if (isset($route['options']['defaults'][$rp->getType()])) {
                return false;
            }
            // Same with bookmark.
            if (isset($route['options']['defaults'][$rp->getBookmark()])) {
                return false;
            }
            return true;
        });
        foreach ($routes as $route) {
            try {
                return $this->urlHelper->generate($route['name'], $this->getRouteParams($link));
            } catch (RouterException $e) { }
        }

        return null;
    }

    /**
     * Try to find the best match for the link based on document type
     *
     * In order to resolve a single document, the route must reference not only the
     * document type but also either the Uid or the Id. Routes are evaluated in FIFO
     * order, prefering routes that match the UID over the ID
     *
     * @param DocumentLink $link
     * @return string|null
     */
    protected function tryResolveAsType(DocumentLink $link)
    {
        $idParam   = $this->routeParams->getId();
        $uidParam  = $this->routeParams->getUid();
        $routes    = $this->findRoutesByType($link->getType());

        $uidRoutes = array_filter($routes, function($route) use ($uidParam) {
            return isset($route['options']['defaults'])
                   &&
                   array_key_exists($uidParam, $route['options']['defaults']);
        });

        $idRoutes  = array_filter($routes, function($route) use ($idParam) {
            return isset($route['options']['defaults'])
                   &&
                   array_key_exists($idParam, $route['options']['defaults']);
        });

        $params = $this->getRouteParams($link);

        foreach ($uidRoutes as $route) {
            try {
                return $this->urlHelper->generate($route['name'], $params);
            } catch (RouterException $e) { }
        }

        foreach ($idRoutes as $route) {
            try {
                return $this->urlHelper->generate($route['name'], $params);
            } catch (RouterException $e) { }
        }

        return null;
    }

    /**
     * Resolving with a bookmark is the easiest thing to do…
     *
     * As a bookmark can only refer to a single document, a bookmark
     * should only be set for a single route, so the first route
     * with a matching bookmark is returned.
     * @param DocumentLink $link
     * @return null|string
     */
    protected function tryResolveAsBookmark(DocumentLink $link)
    {
        $bookmark = $this->getBookmarkNameWithLink($link);
        if (!$bookmark) {
            return null;
        }
        $route = $this->findRouteNameWithBookmark($bookmark);
        if (!$route) {
            return null;
        }

        return $this->urlHelper->generate($route, $this->getRouteParams($link));
    }

    /**
     * Return all routes that refer to the given document type
     * @param string $type
     * @return array
     */
    protected function findRoutesByType(string $type) : array
    {
        $search = $this->routeParams->getType();
        return array_filter($this->routeConfig, function($route) use ($search, $type) {
            return isset($route['options']['defaults'][$search])
                   &&
                   ($route['options']['defaults'][$search] === $type);
        });
    }

    /**
     * @param string $bookmark
     * @return string|null
     */
    protected function findRouteNameWithBookmark(string $bookmark)
    {
        $search = $this->routeParams->getBookmark();
        foreach ($this->routeConfig as $route) {
            $param = isset($route['options']['defaults'][$search]) ? $route['options']['defaults'][$search] : null;
            $name = isset($route['name']) ? $route['name'] : null;
            if ($param === $bookmark) {
                // Route name might be null, but we won't be able to assemble a route without the name anyway
                return $name;
            }
        }

        return null;
    }

    /**
     * Return route params used to assemble a url for the given link
     * @param DocumentLink $link
     * @return array
     */
    protected function getRouteParams(DocumentLink $link) : array
    {
        $params = [];
        $params[$this->routeParams->getId()]       = $link->getId();
        $params[$this->routeParams->getUid()]      = $link->getUid();
        $params[$this->routeParams->getType()]     = $link->getType();
        $params[$this->routeParams->getBookmark()] = $this->getBookmarkNameWithLink($link);
        return $params;
    }

    /**
     * @param Prismic\Document $document
     * @return null|string
     */
    protected function getBookmarkNameWithDocument(Prismic\Document $document)
    {
        return $this->getBookmarkNameWithLink($this->asLink($document));
    }

    /**
     * This method convert a document into document link
     *
     * @param Prismic\Document $document The document
     *
     * @return DocumentLink The document link
     */
    protected function asLink($document)
    {
        return new DocumentLink(
            $document->getId(),
            $document->getUid(),
            $document->getType(),
            $document->getTags(),
            $document->getSlug(),
            $document->getFragments(),
            false
        );
    }

    /**
     * Return a bookmark name if it corresponds to the document id in $link
     * @param DocumentLink $link
     * @return string|null
     */
    public function getBookmarkNameWithLink(DocumentLink $link)
    {
        $bookmarks = array_flip($this->api->bookmarks());
        $id = $link->getId();
        return isset($bookmarks[$id]) ? $bookmarks[$id] : null;
    }

}
