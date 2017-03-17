<?php

namespace ExpressivePrismic;

use Zend\Expressive\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\Route;

class MockRouter implements RouterInterface
{

    public $routes = [];

    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }

    public function addRoute(Route $route)
    {

    }

    public function match(Request $request)
    {

    }

    public function generateUri($name, array $substitutions = [], array $options = [])
    {


        if (isset($this->routes[$name])) {
            $return = json_encode([
                'routeName' => $name,
                'url' => $this->routes[$name],
                'params' => $substitutions
            ]);
            return $return;
        }

        throw new \Exception\RuntimeException('Cannot find given route: '.$name);
    }
}
