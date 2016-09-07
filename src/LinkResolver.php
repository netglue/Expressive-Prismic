<?php
declare(strict_types=1);
namespace ExpressivePrismic;

use Prismic;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;

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

    private $urlHelper;



    public function __construct(Prismic\Api $api, RouteParams $params, array $routeConfig, $urlHelper)
    {
        $this->api = $api;
        $this->routeParams = $params;
        $this->routeConfig = $routeConfig;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return string|null
     */
    public function resolve($link)
    {
        if (!$link instanceof LinkInterface) {
            return;
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

    protected function tryResolveAsId($link)
    {
        $route = $this->findIdRoute($type);
        if (!$route) {
            return null;
        }
        return $this->urlHelper->generate($route, $this->getRouteParams($link));
    }

    protected function tryResolveAsType($link)
    {
        $type = $link->getType();
        $uid = $link->getUid();
        if (!$type || !$uid) {
            return null;
        }
        $route = $this->findRouteNameByType($type);
        if (!$route) {
            return null;
        }

        return $this->urlHelper->generate($route, $this->getRouteParams($link));
    }

    protected function tryResolveAsBookmark($link)
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

    protected function findIdRoute()
    {
        $id = $this->routeParams->getId();
        foreach ($this->routeConfig as $route) {
            if (!isset($route['options']['defaults'][$id])) {
                continue;
            }
            return isset($route['name']) ? $route['name'] : null;
        }
    }

    protected function findRouteNameByType($type)
    {
        $search = $this->routeParams->getType();
        foreach ($this->routeConfig as $route) {
            $param = isset($route['options']['defaults'][$search]) ? $route['options']['defaults'][$search] : null;
            $name = isset($route['name']) ? $route['name'] : null;
            if ($param === $type) {
                // Route name might be null, but we won't be able to assemble a route without the name anyway
                return $name;
            }
        }
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
    }

    protected function getRouteParams(DocumentLink $link) : array
    {
        $params = [];
        $params[$this->routeParams->getId()] = $link->getId();
        $params[$this->routeParams->getUid()] = $link->getUid();
        $params[$this->routeParams->getType()] = $link->getType();
        $params[$this->routeParams->getBookmark()] = $this->getBookmarkNameWithLink($link);
        return $params;
    }

    protected function getBookmarkNameWithDocument(Prismic\Document $document)
    {
        return $this->getBookmarkNameWithLink($this->asLink($document));
    }

    /**
     * This method convert a document into document link
     *
     * @param Document $document The document
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

    public function getBookmarkNameWithLink(DocumentLink $link)
    {
        $bookmarks = array_flip($this->api->bookmarks());
        $id = $link->getId();
        return isset($bookmarks[$id]) ? $bookmarks[$id] : null;
    }

}
