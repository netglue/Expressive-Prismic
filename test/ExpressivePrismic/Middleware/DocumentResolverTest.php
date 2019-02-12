<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Exception\RuntimeException;
use ExpressivePrismic\Middleware\DocumentResolver;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Zend\Expressive\Router\RouteResult;

class DocumentResolverTest extends TestCase
{


    private $api;
    private $routeParams;
    private $docRegistry;
    private $routeResult;
    private $delegate;
    private $request;
    private $document;

    public function setUp() : void
    {
        $this->api         = $this->prophesize(Prismic\Api::class);
        $this->routeParams = new RouteParams([]);
        $this->docRegistry = $this->prophesize(CurrentDocument::class);
        $this->delegate    = $this->prophesize(DelegateInterface::class);
        $this->request     = $this->prophesize(Request::class);
        $this->routeResult = $this->prophesize(RouteResult::class);
        $this->document    = $this->prophesize(Prismic\DocumentInterface::class);
    }

    private function getMiddleware() : DocumentResolver
    {
        return new DocumentResolver(
            $this->api->reveal(),
            $this->routeParams,
            $this->docRegistry->reveal()
        );
    }

    public function testExceptionIsThrownWhenNoRouteResultIsPresent() : void
    {
        $this->request->getAttribute(RouteResult::class)->willReturn(null);
        $middleware = $this->getMiddleware();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No route has yet been matched');
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }

    public function testResolveWithBookmark() : void
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
             ->getById('documentId')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\DocumentInterface::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->handle($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );
    }

    public function testResolveWithId() : void
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([
                'prismic-id' => 'some-id',
             ]);

        $document = $this->document->reveal();

        $this->api
             ->getById('some-id')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\DocumentInterface::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->handle($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );
    }

    public function testResolveWithUid() : void
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([
                'prismic-uid' => 'some-uid',
                'prismic-type' => 'some-type',
             ]);

        $document = $this->document->reveal();

        $this->api
             ->getByUid('some-type', 'some-uid')
             ->willReturn($document);

        $this->docRegistry->setDocument($document)->shouldBeCalled();

        $anotherRequest = $this->prophesize(Request::class)->reveal();
        $this->request->withAttribute(Prismic\DocumentInterface::class, $document)->willReturn($anotherRequest);

        $request = $this->request->reveal();

        $this->delegate->handle($anotherRequest)->shouldBeCalled();

        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $middleware = $this->getMiddleware();
        $middleware->process(
            $request,
            $this->delegate->reveal()
        );
    }

    public function testDelegateProcessesWhenTheresNoDocument() : void
    {
        $this->routeResult
            ->getMatchedParams()
            ->willReturn([]);

        $this->docRegistry->setDocument()->shouldNotBeCalled();
        $this->request->withAttribute()->shouldNotBeCalled();
        $this->request->getAttribute(RouteResult::class)->willReturn($this->routeResult->reveal());
        $this->delegate->handle(Argument::type(Request::class))->shouldBeCalled();


        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }
}
