<?php
declare(strict_types=1);

namespace ExpressivePrismic;

use ExpressivePrismic\Exception;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Router\Route;

class RouteMatcher
{

    /**
     * @var RouteParams
     */
    private $routeParams;

    /**
     * @var [Route]
     */
    private $routes;

    /**
     * @var [Route]
     */
    private $bookmarks;

    /**
     * @var [Route]
     */
    private $typed;

    public function __construct(array $routes, RouteParams $routeParams)
    {
        $this->routeParams = $routeParams;
        $this->routes      = $routes;

        $this->extractBookmarked();
        $this->extractByType();
    }

    public function getBookmarkedRoute(string $bookmark) :? Route
    {
        return isset($this->bookmarks[$bookmark])
               ? $this->bookmarks[$bookmark]
               : null;
    }

    public function getTypedRoute(string $type) :? Route
    {
        return isset($this->typed[$type])
               ? $this->typed[$type]
               : null;
    }

    private function extractBookmarked() : void
    {
        $search = $this->routeParams->getBookmark();
        $this->bookmarks = [];
        foreach ($this->routes as $key => $route) {
            $options = $route->getOptions();
            if (!empty($options['defaults'][$search])) {
                $this->bookmarks[$options['defaults'][$search]] = $route;
                // This route can only possibly match a single bookmarked document
                // so remove it from further evaluation
                unset($this->routes[$key]);
            }
        }
    }

    private function extractByType() : void
    {
        $search = $this->routeParams->getType();
        $this->typed = [];
        foreach ($this->routes as $key => $route) {
            $options = $route->getOptions();
            $type = isset($options['defaults'][$search])
                  ? $options['defaults'][$search]
                  : null;
            if ($type) {
                $this->addTypedRoute($type, $route);
                // Only documents matching the given type will ever match
                // so remove from further evaluation
                unset($this->routes[$key]);
            }
        }
    }

    private function addTypedRoute($type, Route $route) : void
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                $this->addTypedRoute($t, $route);
            }
            return;
        }
        if (!is_string($type)) {
            throw new Exception\InvalidArgumentException('Route type definitions for Prismic routes must be a string or an array of strings');
        }
        $this->typed[$type] = $route;
    }
}
