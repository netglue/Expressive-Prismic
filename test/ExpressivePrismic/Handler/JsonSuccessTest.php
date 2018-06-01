<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Handler;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Handler\JsonSuccess;

// Deps
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccessTest extends TestCase
{

    public function testJsonReturned()
    {
        $request = $this->prophesize(Request::class)->reveal();

        $middleware = new JsonSuccess;

        $response = $middleware->handle($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}