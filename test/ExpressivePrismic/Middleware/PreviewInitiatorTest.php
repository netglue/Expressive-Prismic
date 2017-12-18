<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\PreviewInitiator;

// Deps
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\ServerRequest;
use Prismic;
use Zend\Diactoros\Response\RedirectResponse;

class PreviewInitiatorTest extends TestCase
{

    private $delegate;
    private $request;
    private $api;
    private $resolver;

    public function setUp()
    {
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->resolver = $this->prophesize(Prismic\LinkResolver::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware()
    {
        return new PreviewInitiator(
            $this->api->reveal(),
            $this->resolver->reveal()
        );
    }

    public function testAbsenceOfQueryStringIsANoop()
    {
        $this->request->getQueryParams()->willReturn([]);
        $request = $this->request->reveal();
        $this->delegate->process($request)->shouldBeCalled();
        $this->api->previewSession()->shouldNotBeCalled();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testResponseIsRedirectWithCookie()
    {
        $this->request->getQueryParams()->willReturn(['token' => 'Some%20Token']);

        $this->api->previewSession('Some Token', Argument::type(Prismic\LinkResolver::class), Argument::type('string'))
            ->willReturn('/some-url');
        $this->delegate->process()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertSame(302, $response->getStatusCode());

        $header = $response->getHeader('location');
        $this->assertSame('/some-url', current($header));
        $this->assertTrue($response->hasHeader('Set-Cookie'));
    }

}
