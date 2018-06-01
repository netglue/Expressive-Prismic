<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Middleware\JsonSuccess;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccessTest extends TestCase
{

    public function testJsonReturned()
    {
        $request = $this->prophesize(Request::class)->reveal();
        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->handle()->shouldNotBeCalled();

        $middleware = new JsonSuccess;

        $response = $middleware->process($request, $delegate->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}
