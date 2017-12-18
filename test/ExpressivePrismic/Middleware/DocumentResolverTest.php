<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\DocumentResolver;

// Deps

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Expressive\Router\RouteResult;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\Exception\PageNotFoundException;

class DocumentResolverTest extends TestCase
{


    private $api;
    private $routeParams;
    private $docRegistry;
    private $routeResult;
    private $delegate;
    private $request;
    private $document;

    public function setUp()
    {
        $this->api         = $this->prophesize(Prismic\Api::class);
        $this->routeParams = new RouteParams([]);
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->delegate    = $this->prophesize(DelegateInterface::class);
        $this->request     = $this->prophesize(Request::class);
        $this->routeResult = $this->prophesize(RouteResult::class);
        $this->document    = $this->prophesize(Prismic\Document::class);
    }

    public function getMiddleware()
    {
        return new DocumentResolver(
            $this->api->reveal(),
            $this->routeParams,
            $this->docRegistry->reveal()
        );
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No route has yet been matched
     */
    public function testExceptionIsThrownWhenNoRouteResultIsPresent()
    {
        $this->request->getAttribute(RouteResult::class)->willReturn(null);
        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }

    public function testResolveWithBookmark()
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([
                'prismic-bookmark' => 'test',
             ]);

        $this->api
             ->bookmark('test')
             ->willReturn('documentId');

        $document = $this->document->reveal();

        $this->api
             ->getByID('documentId')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\Document::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->process($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );
    }

    public function testResolveWithId()
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([
                'prismic-id' => 'some-id',
             ]);

        $document = $this->document->reveal();

        $this->api
             ->getByID('some-id')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\Document::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->process($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );
    }

    public function testResolveWithUid()
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([
                'prismic-uid' => 'some-uid',
                'prismic-type' => 'some-type',
             ]);

        $document = $this->document->reveal();

        $this->api
             ->getByUID('some-type', 'some-uid')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\Document::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->process($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );

    }

    public function testDelegateProcessesWhenTheresNoDocument()
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([]);

        $this->docRegistry->setDocument()->shouldNotBeCalled();
        $this->request->withAttribute()->shouldNotBeCalled();
        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $this->delegate->process(Argument::type(Request::class))->shouldBeCalled();


        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }


}

