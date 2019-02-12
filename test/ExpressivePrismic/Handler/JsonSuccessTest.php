<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Handler;

use ExpressivePrismic\Handler\JsonSuccess;
use ExpressivePrismicTest\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccessTest extends TestCase
{

    public function testJsonReturned() : void
    {
        $request = $this->prophesize(Request::class)->reveal();

        $middleware = new JsonSuccess;

        $response = $middleware->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testJsonIsReturnedFromProcess() : void
    {
        $request = $this->prophesize(Request::class)->reveal();

        $middleware = new JsonSuccess;
        /** @var RequestHandlerInterface $delegate */
        $delegate = $this->prophesize(RequestHandlerInterface::class);
        $delegate->handle()->shouldNotBeCalled();
        $response = $middleware->process($request, $delegate->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}
