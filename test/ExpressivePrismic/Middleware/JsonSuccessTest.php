<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\JsonSuccess;

// Deps
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccessTest extends TestCase
{

    public function testJsonReturned()
    {
        $request = $this->prophesize(Request::class)->reveal();
        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process()->shouldNotBeCalled();

        $middleware = new JsonSuccess;

        $response = $middleware->process($request, $delegate->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}
