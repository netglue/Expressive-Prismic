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

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;

class DocumentResolverTest extends \PHPUnit_Framework_TestCase
{

    private $api;
    private $routeResult;
    private $request;
    private $resolver;
    private $document;
    private $docRegistry;

    public function setUp()
    {
        $this->api = $this->createMock(Prismic\Api::class);
        $this->routeResult = $this->createMock(RouteResult::class);
        $request = new ServerRequest;
        $this->request = $request->withAttribute(RouteResult::class, $this->routeResult);
        $this->docRegistry = new CurrentDocument;
        $this->resolver = new DocumentResolver($this->api, new RouteParams, $this->docRegistry);

        $this->document = new Prismic\Document('id', 'uid', 'type', 'href', ['tag'], ['slugs'], 'lang', ['alternateLang'], [/*$fragments*/], '{"json":"data"}');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No route has yet been matched
     */
    public function testExceptionIsThrownWhenNoRouteResultIsPresent()
    {
        $resolver = new DocumentResolver($this->api, new RouteParams, new CurrentDocument);
        $resolver->process(new ServerRequest, new DelegateMock);
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
             ->willReturn($this->document);

        $delegate = new DelegateMock;
        $this->resolver->process($this->request, $delegate);
        $this->assertInstanceOf(Prismic\Document::class, $delegate->request->getAttribute(Prismic\Document::class));
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
             ->willReturn($this->document);

        $delegate = new DelegateMock;
        $this->assertFalse($this->docRegistry->hasDocument());
        $this->resolver->process($this->request, $delegate);
        $this->assertInstanceOf(Prismic\Document::class, $delegate->request->getAttribute(Prismic\Document::class));
        $this->assertInstanceOf(Prismic\Document::class, $this->docRegistry->getDocument());
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
             ->willReturn($this->document);

        $delegate = new DelegateMock;
        $this->resolver->process($this->request, $delegate);
        $this->assertInstanceOf(Prismic\Document::class, $delegate->request->getAttribute(Prismic\Document::class));
    }

    /**
     * @expectedException ExpressivePrismic\Exception\PageNotFoundException
     */
    public function test404ThrownWithNoMatch()
    {
        $delegate = new DelegateMock;
        $this->resolver->process($this->request, $delegate);
    }


}

