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
use ExpressivePrismic\Service\CurrentDocument;
use Prismic;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\Utils;
use Zend\Diactoros\Response\TextResponse;


class ErrorResponseGeneratorTest extends TestCase
{
    private $api;
    private $docRegistry;
    private $request;
    private $pipe;

    public function setUp()
    {
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->request  = $this->prophesize(Request::class);
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->pipe = $this->prophesize(MiddlewarePipe::class);
    }

    public function getMiddleware()
    {
        return new ErrorResponseGenerator(
            $this->pipe->reveal(),
            $this->api->reveal(),
            $this->docRegistry->reveal(),
            'bookmark',
            'template-name'
        );
    }

    public function testThatThePipeWillBeProcessedIfADocumentCanBeLocated()
    {
        $doc = $this->prophesize(Prismic\Document::class);
        $doc = $doc->reveal();
        $this->api->bookmark('bookmark')->willReturn('An-ID');
        $this->api->getByID('An-ID')->willReturn($doc);
        $this->docRegistry->setDocument($doc)->shouldBeCalled();
        $this->request->withAttribute(Prismic\Document::class, $doc)->willReturn($this->request->reveal());
        $this->request->withAttribute('template', 'template-name')->willReturn($this->request->reveal());

        $response = new TextResponse('Some Text');
        $this->pipe->process($this->request->reveal(), Argument::type(ErrorResponseGenerator::class))
            ->willReturn($response);

        $middleware = $this->getMiddleware();
        $originalResponse = $this->prophesize(Response::class)->reveal();
        $result = $middleware(new \Exception('Foo'), $this->request->reveal(), $originalResponse);
        $this->assertSame(500, $result->getStatusCode());
    }

    public function testThatTheFallbackResponseWillBeEmittedIfTheDocumentBookmarkReturnsANullID()
    {
        $this->api->bookmark('bookmark')->willReturn(null);
        $this->docRegistry->setDocument()->shouldNotBeCalled();
        $this->pipe->process()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $originalResponse = $this->prophesize(Response::class)->reveal();
        $result = $middleware(new \Exception('Foo'), $this->request->reveal(), $originalResponse);
        $this->assertSame(500, $result->getStatusCode());
        $this->assertSame('An Unexpected Error Occurred', (string) $result->getBody());
    }

    public function testThatTheFallbackResponseWillBeEmittedIfTheDocumentIdIsInvalid()
    {
        $this->api->bookmark('bookmark')->willReturn('An-ID');
        $this->api->getByID('An-ID')->willReturn(null);

        $this->docRegistry->setDocument()->shouldNotBeCalled();
        $this->pipe->process()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $originalResponse = $this->prophesize(Response::class)->reveal();
        $result = $middleware(new \Exception('Foo'), $this->request->reveal(), $originalResponse);
        $this->assertSame(500, $result->getStatusCode());
        $this->assertSame('An Unexpected Error Occurred', (string) $result->getBody());
    }

    public function testThatTheFallbackResponseWillBeEmittedIfThePipeThrowsAnException()
    {
        $doc = $this->prophesize(Prismic\Document::class);
        $doc = $doc->reveal();
        $this->api->bookmark('bookmark')->willReturn('An-ID');
        $this->api->getByID('An-ID')->willReturn($doc);
        $this->docRegistry->setDocument($doc)->shouldBeCalled();
        $this->request->withAttribute(Prismic\Document::class, $doc)->willReturn($this->request->reveal());
        $this->request->withAttribute('template', 'template-name')->willReturn($this->request->reveal());
        $this->pipe->process()->will(function() {
            throw new \Exception('foo');
        });

        $middleware = $this->getMiddleware();
        $originalResponse = $this->prophesize(Response::class)->reveal();
        $result = $middleware(new \Exception('Original Exception'), $this->request->reveal(), $originalResponse);
        $this->assertSame(500, $result->getStatusCode());
        $this->assertSame('An Unexpected Error Occurred', (string) $result->getBody());
    }

}
