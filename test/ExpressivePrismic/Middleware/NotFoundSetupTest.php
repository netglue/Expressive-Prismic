<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Middleware\NotFoundSetup;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\Diactoros\Response as ServerResponse;
use ExpressivePrismic\Service\CurrentDocument;

class NotFoundSetupTest extends TestCase
{

    private $delegate;
    private $request;
    private $api;
    private $currentDoc;

    public function setUp()
    {
        $this->api        = $this->prophesize(Prismic\Api::class);
        $this->currentDoc = $this->prophesize(CurrentDocument::class);
        $this->delegate   = $this->prophesize(DelegateInterface::class);
        $this->request    = $this->prophesize(Request::class);
    }

    public function getMiddleware()
    {
        return new NotFoundSetup(
            $this->api->reveal(),
            $this->currentDoc->reveal(),
            'some-bookmark',
            'some-template'
        );
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Error document bookmark does not reference a current document ID
     */
    public function testExceptionThrownForInvalidBookmark()
    {
        $this->api->bookmark('some-bookmark')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Error document cannot be resolved
     */
    public function testExceptionThrownForInvalidDocument()
    {
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getById('some-id')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->handle()->shouldNotBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage An exception occurred retrieving the error document
     */
    public function testApiExceptionIsWrapped()
    {
        $exception = new \Prismic\Exception\RequestFailureException();
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getById('some-id')->willThrow($exception);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testSuccessfulDocumentRetrievalWillBeAddedToRequestAttrs()
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
