<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\ErrorResponseGenerator;

// Deps
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Response\TextResponse;
use Zend\Stratigility\MiddlewarePipeInterface;

class ErrorResponseGeneratorTest extends TestCase
{

    /** @var MiddlewarePipe */
    private $pipe;

    public function setUp()
    {
        $this->pipe = $this->prophesize(MiddlewarePipeInterface::class);
    }

    public function getMiddleware()
    {
        return new ErrorResponseGenerator(
            $this->pipe->reveal()
        );
    }

    public function testThatInvokeProcessesPipe()
    {
        $response = new TextResponse('Some Text');
        $this->pipe->process(
            Argument::any(),
            Argument::type(ErrorResponseGenerator::class)
        )->willReturn($response);

        $handler = $this->getMiddleware();

        $originalRequest = $this->prophesize(Request::class)->reveal();
        $originalResponse = $this->prophesize(Response::class)->reveal();

        $result = $handler(new \Exception('Message'), $originalRequest, $originalResponse);
        $this->assertSame( 'Some Text', (string) $result->getBody());
        $this->assertSame(500, $result->getStatusCode());
    }

    public function testThatExceptionDuringProcessRendersFallbackTextResponse()
    {
        $this->pipe->process(
            Argument::any(),
            Argument::type(ErrorResponseGenerator::class)
        )->willThrow(new \Exception('Uncaught'));

        $handler = $this->getMiddleware();
        $originalRequest = $this->prophesize(Request::class)->reveal();
        $originalResponse = $this->prophesize(Response::class)->reveal();

        $result = $handler(new \Exception('Message'), $originalRequest, $originalResponse);

        $this->assertInstanceOf(TextResponse::class, $result);
        $this->assertSame('An Unexpected Error Occurred', (string) $result->getBody());
        $this->assertSame(500, $result->getStatusCode());
    }
}
