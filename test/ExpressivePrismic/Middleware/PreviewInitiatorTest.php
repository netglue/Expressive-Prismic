<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\PreviewInitiator;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Prismic;
use Zend\Diactoros\Response\RedirectResponse;

class PreviewInitiatorTest extends TestCase
{

    private $delegate;
    private $request;
    private $api;
    private $uri;

    public function setUp()
    {
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
        $this->uri      = $this->prophesize(UriInterface::class);
    }

    public function getMiddleware()
    {
        return new PreviewInitiator(
            $this->api->reveal()
        );
    }

    public function testAbsenceOfQueryStringIsANoop()
    {
        $this->request->getQueryParams()->willReturn([]);
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();
        $this->api->previewSession()->shouldNotBeCalled();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    private function prepareRequest()
    {
        $this->uri->getScheme()->willReturn('https');
        $this->uri->getHost()->willReturn('foo.com');
        $this->request->getUri()->willReturn($this->uri->reveal());
        $this->request->getQueryParams()->willReturn(['token' => 'Some%20Token']);
        $this->api->previewSession('Some Token', Argument::type('string'))
             ->willReturn('/some-url');
    }

    public function testResponseIsRedirectWithCookie()
    {
        $this->prepareRequest();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(302, $response->getStatusCode());

        $header = $response->getHeader('location');
        $this->assertSame('/some-url', current($header));
        $this->assertTrue($response->hasHeader('Set-Cookie'));
    }

    public function testCookieHasDomain()
    {
        $this->prepareRequest();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $header = $response->getHeader('Set-Cookie');
        $header = current($header);

        $this->assertContains('foo.com', $header);
        $this->assertContains('Secure', $header);
    }
}
