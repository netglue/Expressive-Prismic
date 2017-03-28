<?php
declare(strict_types = 1);
namespace ExpressivePrismic;

use Prismic;
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
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var RouteParams
     */
    private $routeParams;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Application
     */
    private $app;

    /**
     * LinkResolver constructor.
     *
     * @param Prismic\Api $api        Api is used for querying the available bookmarks
     * @param RouteParams $params     This object is all about knowing what route parameter names to look for
     * @param UrlHelper   $urlHelper  This helper generates the actual URLs
     * @param Application $app        The app is needed to retrieve the configured routes
     */
    public function __construct(Prismic\Api $api, RouteParams $params, UrlHelper $urlHelper, Application $app)
    {
        $this->api         = $api;
        $this->routeParams = $params;
        $this->urlHelper   = $urlHelper;
        $this->app         = $app;
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

    /**
     * The most generic route would reference only the id
     *
     * @param DocumentLink $link
     * @return string|null
     */
    protected function tryResolveAsId(DocumentLink $link)
    {
        $routes = array_filter($this->app->getRoutes(), function ($route) {
            $rp = $this->routeParams;
            $options = $route->getOptions();
            if (!isset($options['defaults'])) {
                return false;
            }
            if (!array_key_exists($rp->getId(), $options['defaults'])) {
                return false;
            }
            // If ID & Type are available, it means the route previously didn't match
            if (isset($options['defaults'][$rp->getType()])) {
                return false;
            }
            // Same with bookmark.
            if (isset($options['defaults'][$rp->getBookmark()])) {
                return false;
            }

            return true;
        });
        foreach ($routes as $route) {
            try {
                return $this->urlHelper->generate($route->getName(), $this->getRouteParams($link));
            } catch (RouterException $e) {
            }
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
        foreach ($this->findRoutesByType($link->getType()) as $route) {
            try {
                return $this->urlHelper->generate($route->getName(), $this->getRouteParams($link));
            } catch (RouterException $e) {
            }
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
        $routeName = $this->findRouteNameWithBookmark($bookmark);
        if (!$routeName) {
            return null;
        }

        return $this->urlHelper->generate($routeName, $this->getRouteParams($link));
    }

    /**
     * Return all routes that refer to the given document type
     * @param string $type
     * @return array
     */
    protected function findRoutesByType(string $type) : array
    {
        $search = $this->routeParams->getType();

        return array_filter($this->app->getRoutes(), function ($route) use ($search, $type) {
            $options = $route->getOptions();
            return isset($options['defaults'][$search])
                   &&
                   ($options['defaults'][$search] === $type);
        });
    }

    /**
     * @param string $bookmark
     * @return string|null
     */
    protected function findRouteNameWithBookmark(string $bookmark)
    {
        $search = $this->routeParams->getBookmark();
        foreach ($this->app->getRoutes() as $route) {
            $options = $route->getOptions();
            $param = isset($options['defaults'][$search]) ? $options['defaults'][$search] : null;
            if ($param === $bookmark) {
                // Route name might be null, but we won't be able to assemble a route without the name anyway
                return $route->getName();
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
        return $this->getBookmarkNameWithLink($document->asDocumentLink());
    }

}
