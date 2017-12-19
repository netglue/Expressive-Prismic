<?php
declare(strict_types = 1);
namespace ExpressivePrismic;

use Prismic;
use Prismic\Document;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\ExceptionInterface as RouterException;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;

/**
 * Prismic LinkResolver Implementation
 *
 * @package ExpressivePrismic
 */
class LinkResolver extends Prismic\LinkResolver
{

    /**
     * @var array
     */
    private $bookmarks;

    /**
     * @var RouteParams
     */
    private $routeParams;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var RouteMatcher
     */
    private $routeMatch;

    public function __construct(array $bookmarks, RouteParams $params, UrlHelper $urlHelper, RouteMatcher $routeMatch)
    {
        $this->bookmarks   = $bookmarks;
        $this->routeParams = $params;
        $this->urlHelper   = $urlHelper;
        $this->routeMatch  = $routeMatch;
    }

    /**
     * @param LinkInterface $link
     * @return string|null
     */
    public function resolve($link)
    {
        if ($link instanceof Document) {
            return $this->resolveDocument($link);
        }

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

            return null;
        }

        return $link->getUrl($this);
    }

    /**
     * Return a bookmark name if it corresponds to the document id in $link
     * @param DocumentLink $link
     * @return string|null
     */
    public function getBookmarkNameWithLink(DocumentLink $link)
    {
        $bookmarks = array_flip($this->bookmarks);
        $id = $link->getId();

        return isset($bookmarks[$id]) ? $bookmarks[$id] : null;
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
        $route = $this->routeMatch->getTypedRoute($link->getType());
        if ($route) {
            return $this->urlHelper->generate($route->getName(), $this->getRouteParams($link));
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
        $route = $this->routeMatch->getBookmarkedRoute($bookmark);
        if (!$route) {
            return null;
        }

        return $this->urlHelper->generate($route->getName(), $this->getRouteParams($link));
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
        $params[$this->routeParams->getLang()]     = $link->getLang();

        return $params;
    }

}
