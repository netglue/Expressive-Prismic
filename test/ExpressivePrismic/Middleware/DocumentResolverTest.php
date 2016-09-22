<?php

namespace ExpressivePrismic\Middleware;

use Prismic;
use ExpressivePrismic\Service\CurrentDocument;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

use ExpressivePrismic\Middleware\DocumentResolver;
use ExpressivePrismic\Service\RouteParams;

class DocumentResolverTest extends \PHPUnit_Framework_TestCase
{

    private $api;
    private $routeResult;
    private $request;
    private $resolver;

    public function setUp()
    {
        $this->api = $this->createMock(Prismic\Api::class);
        $this->routeResult = $this->createMock(RouteResult::class);
        $request = new ServerRequest;
        $this->request = $request->withAttribute(RouteResult::class, $this->routeResult);
        $this->resolver = new DocumentResolver($this->api, new RouteParams, new CurrentDocument);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No route has yet been matched
     */
    public function testExceptionIsThrownWhenNoRouteResultIsPresent()
    {
        $resolver = new DocumentResolver($this->api, new RouteParams, new CurrentDocument);
        $resolver(new ServerRequest, new Response);
    }

    public function testResolveWithBookmark()
    {
        $this->routeResult
             ->method('getMatchedParams')
             ->willReturn([
                'prismic-bookmark' => 'test',
             ]);

        $this->api
             ->method('bookmark')
             ->willReturn('foo');

        $this->api
             ->method('getByID')
             ->willReturn(new Prismic\Document('foo', 'foo', 'foo', 'foo', [], [], [], '{}'));

        $next = function($request, $response) {
            $this->assertInstanceOf(Prismic\Document::class, $request->getAttribute(Prismic\Document::class));
            return $response;
        };

        $response = $this->resolver->__invoke($this->request, new Response, $next);
    }

    public function testResolveWithId()
    {
        $this->routeResult
             ->method('getMatchedParams')
             ->willReturn([
                'prismic-id' => 'test',
             ]);
        $this->api
             ->method('getByID')
             ->willReturn(new Prismic\Document('foo', 'foo', 'foo', 'foo', [], [], [], '{}'));

        $next = function($request, $response) {
            $this->assertInstanceOf(Prismic\Document::class, $request->getAttribute(Prismic\Document::class));
            return $response;
        };

        $response = $this->resolver->__invoke($this->request, new Response, $next);
    }

    public function testResolveWithUid()
    {
        $this->routeResult
             ->method('getMatchedParams')
             ->willReturn([
                'prismic-uid'  => 'test',
                'prismic-type' => 'test',
             ]);
        $this->api
             ->method('getByUID')
             ->willReturn(new Prismic\Document('foo', 'foo', 'foo', 'foo', [], [], [], '{}'));

        $next = function($request, $response) {
            $this->assertInstanceOf(Prismic\Document::class, $request->getAttribute(Prismic\Document::class));
            return $response;
        };

        $response = $this->resolver->__invoke($this->request, new Response, $next);
    }

    public function testPassthroughWithNoMatch()
    {
        $next = function($request, $response) {
            $this->assertNull($request->getAttribute(Prismic\Document::class));
            return $response;
        };
        $response = $this->resolver->__invoke($this->request, new Response, $next);
    }

    public function testPassThroughWithNoNext()
    {
        $inResponse = new Response;
        $this->assertSame($inResponse, $this->resolver->__invoke($this->request, $inResponse));

    }

}
