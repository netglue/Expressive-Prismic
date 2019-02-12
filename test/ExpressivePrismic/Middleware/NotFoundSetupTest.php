<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Exception\RuntimeException;
use ExpressivePrismic\Middleware\NotFoundSetup;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Zend\Diactoros\Response as ServerResponse;

class NotFoundSetupTest extends TestCase
{

    private $delegate;
    private $request;
    private $api;
    private $currentDoc;

    public function setUp() : void
    {
        $this->api        = $this->prophesize(Prismic\Api::class);
        $this->currentDoc = $this->prophesize(CurrentDocument::class);
        $this->delegate   = $this->prophesize(DelegateInterface::class);
        $this->request    = $this->prophesize(Request::class);
    }

    private function getMiddleware() : NotFoundSetup
    {
        return new NotFoundSetup(
            $this->api->reveal(),
            $this->currentDoc->reveal(),
            'some-bookmark',
            'some-template'
        );
    }

    public function testExceptionThrownForInvalidBookmark() : void
    {
        $this->api->bookmark('some-bookmark')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The error document bookmark "some-bookmark" does not reference a current document ID');
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testExceptionThrownForInvalidDocument() : void
    {
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getById('some-id')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('bookmark "some-bookmark" resolved to the id "some-id" but the document cannot be found');
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testApiExceptionIsWrapped() : void
    {
        $exception = new Prismic\Exception\RequestFailureException();
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getById('some-id')->willThrow($exception);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();
        $middleware = $this->getMiddleware();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An exception occurred retrieving the error document with the id "some-id"');
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testSuccessfulDocumentRetrievalWillBeAddedToRequestAttrs() : void
    {
        $doc = $this->prophesize(Prismic\DocumentInterface::class);
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getById('some-id')->willReturn($doc->reveal());
        $this->currentDoc->setDocument($doc)->shouldBeCalled();
        $this->request->withAttribute(Prismic\DocumentInterface::class, $doc)->willReturn($this->request->reveal());
        $this->request->withAttribute('template', 'some-template')->willReturn($this->request->reveal());
        $this->delegate->handle($this->request->reveal())->willReturn(new ServerResponse);
        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertSame(404, $response->getStatusCode());
    }
}
