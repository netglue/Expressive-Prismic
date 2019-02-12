<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Exception\DocumentNotFoundException;
use ExpressivePrismic\Middleware\ErrorResponseGenerator;
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\TextResponse;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\MiddlewarePipeInterface;

class ErrorResponseGeneratorTest extends TestCase
{

    /** @var MiddlewarePipe */
    private $errorPipe;
    /** @var MiddlewarePipe */
    private $notFoundPipe;

    public function setUp() : void
    {
        $this->errorPipe    = $this->prophesize(MiddlewarePipeInterface::class);
        $this->notFoundPipe = $this->prophesize(MiddlewarePipeInterface::class);
    }

    private function getMiddleware() : ErrorResponseGenerator
    {
        return new ErrorResponseGenerator(
            $this->errorPipe->reveal(),
            $this->notFoundPipe->reveal()
        );
    }

    public function testThatInvokeProcessesErrorPipe() : void
    {
        $response = new TextResponse('Some Text');
        $this->errorPipe->process(
            Argument::any(),
            Argument::type(ErrorResponseGenerator::class)
        )->willReturn($response);
        $this->notFoundPipe->process(Argument::any())->shouldNotBeCalled();
        $handler = $this->getMiddleware();

        $originalRequest = $this->prophesize(Request::class)->reveal();
        $originalResponse = $this->prophesize(Response::class)->reveal();

        $result = $handler(new \Exception('Message'), $originalRequest, $originalResponse);
        $this->assertSame( 'Some Text', (string) $result->getBody());
        $this->assertSame(500, $result->getStatusCode());
    }

    public function testThatExceptionDuringProcessRendersFallbackTextResponse() : void
    {
        $this->errorPipe->process(
            Argument::any(),
            Argument::type(ErrorResponseGenerator::class)
        )->willThrow(new \Exception('Uncaught'));
        $this->notFoundPipe->process(Argument::any())->shouldNotBeCalled();
        $handler = $this->getMiddleware();
        $originalRequest = $this->prophesize(Request::class)->reveal();
        $originalResponse = $this->prophesize(Response::class)->reveal();

        $result = $handler(new \Exception('Message'), $originalRequest, $originalResponse);

        $this->assertInstanceOf(TextResponse::class, $result);
        $this->assertSame('An Unexpected Error Occurred', (string) $result->getBody());
        $this->assertSame(500, $result->getStatusCode());
    }

    public function testNotFoundPipeIsProcessedWhenThrowableInstanceofDocumentNotFound() : void
    {
        $response = new TextResponse('Some Text');
        $this->errorPipe->process(Argument::any())->shouldNotBeCalled();
        $this->notFoundPipe->process(
            Argument::any(),
            Argument::type(ErrorResponseGenerator::class)
        )->willReturn($response);
        $handler = $this->getMiddleware();
        $originalRequest = $this->prophesize(Request::class)->reveal();
        $originalResponse = $this->prophesize(Response::class)->reveal();
        $result = $handler(new DocumentNotFoundException('Message'), $originalRequest, $originalResponse);
        $this->assertSame( 'Some Text', (string) $result->getBody());
        $this->assertSame(404, $result->getStatusCode());
    }
}
