<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\NormalizeNotFound;

// Deps
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

class NormalizeNotFoundTest extends TestCase
{

    private $delegate;
    private $request;

    public function setUp()
    {
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware(string $expectedSecret = 'foo')
    {
        return new NormalizeNotFound();
    }

    /**
     * @expectedException ExpressivePrismic\Exception\PageNotFoundException
     */
    public function testExceptionIsThrownWithNullRouteMatch()
    {
        $request = new ServerRequest;
        $middleware = new NormalizeNotFound;
        $this->delegate->process()->shouldNotBeCalled();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testNoopWithRouteMatch()
    {
        $match = $this->prophesize(RouteResult::class)->reveal();
        $request = $this->prophesize(Request::class);
        $request->getAttribute(RouteResult::class)->willReturn($match);
        /** @var Request **/
        $request = $request->reveal();

        $middleware = new NormalizeNotFound;
        $this->delegate->process($request)->shouldBeCalled();
        $middleware->process($request, $this->delegate->reveal());
    }

}
