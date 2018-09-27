<?php
declare(strict_types=1);

namespace ExpressivePrismic;

use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteCollector;
use function in_array;
use function is_array;

class RouteMatcher
{

    /** @var RouteParams */
    private $routeParams;

    /** @var RouteCollector */
    private $routeCollector;

    public function __construct(RouteCollector $collector, RouteParams $routeParams)
    {
        $this->routeParams    = $routeParams;
        $this->routeCollector = $collector;
    }

    public function getBookmarkedRoute(string $bookmark) :? Route
    {
        $search = $this->routeParams->getBookmark();
        foreach ($this->getRoutes() as $route) {
            $options = $route->getOptions();
            if (empty($options['defaults'][$search])) {
                continue;
            }
            if ($options['defaults'][$search] === $bookmark) {
                return $route;
            }
        }
        return null;
    }

    public function getTypedRoute(string $type) :? Route
    {
        foreach ($this->getRoutes() as $route) {
            if ($this->matchesType($route, $type)) {
                return $route;
            }
        }
        return null;
    }

    private function matchesType(Route $route, string $type) : bool
    {
        $search = $this->routeParams->getType();
        $options = $route->getOptions();
        $subject = isset($options['defaults'][$search])
            ? $options['defaults'][$search]
            : null;
        if (! $subject) {
            return false;
        }
        if (is_array($subject) && in_array($type, $subject, true)) {
            return true;
        }
        return ($type === $subject);
    }

    /**
     * @return Route[]
     */
    private function getRoutes() : array
    {
        return $this->routeCollector->getRoutes();
    }
}
