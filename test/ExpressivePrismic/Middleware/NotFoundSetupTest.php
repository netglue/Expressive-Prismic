<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\NotFoundSetup;

// Deps
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\ServerRequest;
use Prismic;
use Zend\Diactoros\Response\RedirectResponse;
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
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->currentDoc = $this->prophesize(CurrentDocument::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware($fallback)
    {
        return new NotFoundSetup(
            $this->api->reveal(),
            $this->currentDoc->reveal(),
            'some-bookmark',
            'some-template',
            $fallback
        );
    }

    public function testDelegateContinuesForInvalidBookmark()
    {
        $this->api->bookmark('some-bookmark')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->process($request)->willReturn(new ServerResponse);

        $middleware = $this->getMiddleware(true);
        $response = $middleware->process($request, $this->delegate->reveal());
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionThrownForInvalidBookmark()
    {
        $this->api->bookmark('some-bookmark')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->process()->shouldNotBeCalled();

        $middleware = $this->getMiddleware(false);
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testDelegateContinuesForInvalidDocument()
    {
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getByID('some-id')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->process($request)->willReturn(new ServerResponse);

        $middleware = $this->getMiddleware(true);
        $response = $middleware->process($request, $this->delegate->reveal());
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     */
    public function testExceptionThrownForInvalidDocument()
    {
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getByID('some-id')->willReturn(null);
        $this->request->withAttribute()->shouldNotBeCalled();
        $request = $this->request->reveal();

        $this->delegate->process()->shouldNotBeCalled();

        $middleware = $this->getMiddleware(false);
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testSuccessfulDocumentRetrievalWillBeAddedToRequestAttrs()
    {
        $doc = $this->prophesize(Prismic\Document::class);
        $this->api->bookmark('some-bookmark')->willReturn('some-id');
        $this->api->getByID('some-id')->willReturn($doc->reveal());
        $this->currentDoc->setDocument($doc)->shouldBeCalled();
        $this->request->withAttribute(Prismic\Document::class, $doc)->willReturn($this->request->reveal());
        $this->request->withAttribute('template', 'some-template')->willReturn($this->request->reveal());
        $this->delegate->process($this->request->reveal())->willReturn(new ServerResponse);
        $middleware = $this->getMiddleware(false);
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertSame(404, $response->getStatusCode());
    }




}
