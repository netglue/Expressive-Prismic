<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Exception\RuntimeException;
use ExpressivePrismic\Middleware\PreviewInitiator;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Prismic\Exception\ExpiredPreviewTokenException;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\ServerRequest;

class PreviewInitiatorTest extends TestCase
{

    private $delegate;
    private $request;

    /** @var Prismic\Api */
    private $api;
    private $uri;

    public function setUp() : void
    {
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->delegate = $this->prophesize(RequestHandlerInterface::class);
        $this->request  = $this->prophesize(Request::class);
        $this->uri      = $this->prophesize(UriInterface::class);
    }

    private function getMiddleware() : PreviewInitiator
    {
        return new PreviewInitiator(
            $this->api->reveal()
        );
    }

    public function testAbsenceOfQueryStringIsANoop() : void
    {
        $this->request->getQueryParams()->willReturn([]);
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();
        $this->api->previewSession()->shouldNotBeCalled();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    private function prepareRequest() : void
    {
        $this->uri->getScheme()->willReturn('https');
        $this->uri->getHost()->willReturn('foo.com');
        $this->request->getUri()->willReturn($this->uri->reveal());
        $this->request->getQueryParams()->willReturn(['token' => 'Some%20Token']);
        $this->api->previewSession('Some Token', Argument::type('string'))
             ->willReturn('/some-url');
    }

    public function testResponseIsRedirect() : void
    {
        $this->prepareRequest();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $this->assertSame(302, $response->getStatusCode());
        $header = $response->getHeader('location');
        $this->assertSame('/some-url', current($header));
    }

    public function testPreviewExpiryIsCaughtWithFriendlyError() : void
    {
        $request = new ServerRequest();
        $request = $request->withQueryParams(['token' => 'foo']);
        $this->api->previewSession('foo', '/')->willThrow(new ExpiredPreviewTokenException);
        $handler = $this->getMiddleware();
        $response = $handler->process($request, $this->delegate->reveal());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertSame(410, $response->getStatusCode());
    }

    public function testThatLessSpecificExceptionsAreReThrown() : void
    {
        $request = new ServerRequest();
        $request = $request->withQueryParams(['token' => 'foo']);
        $this->api->previewSession('foo', '/')->willThrow(new Prismic\Exception\UnexpectedValueException());
        $handler = $this->getMiddleware();
        $this->expectException(RuntimeException::class);
        $handler->process($request, $this->delegate->reveal());
    }
}
