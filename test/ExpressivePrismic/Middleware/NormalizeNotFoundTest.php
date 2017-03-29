<?php

namespace ExpressivePrismic\Middleware;

use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

class NormalizeNotFoundTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException ExpressivePrismic\Exception\PageNotFoundException
     */
    public function testExceptionIsThrownWithNullRouteMatch()
    {
        $request = new ServerRequest;
        $middleware = new NormalizeNotFound;
        $delegate = new DelegateMock;
        $middleware->process($request, $delegate);
    }

    public function testExceptionNotThrownWithMatchedRoute()
    {
        $request = new ServerRequest;
        $request = $request->withAttribute(RouteResult::class, 'foo');
        $middleware = new NormalizeNotFound;
        $delegate = new DelegateMock;
        $middleware->process($request, $delegate);
        $this->assertSame($request, $delegate->request);
    }

}

