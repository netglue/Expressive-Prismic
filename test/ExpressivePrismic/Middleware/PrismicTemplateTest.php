<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\PrismicTemplate;

// Deps
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic\LinkResolver;
use Prismic\Document;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\HtmlResponse;

class PrismicTemplateTest extends TestCase
{

    private $delegate;
    private $request;
    private $resolver;
    private $renderer;

    public function setUp()
    {
        $this->resolver = $this->prophesize(LinkResolver::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
        $this->renderer  = $this->prophesize(TemplateRendererInterface::class);
    }

    public function getMiddleware()
    {
        return new PrismicTemplate(
            $this->renderer->reveal(),
            $this->resolver->reveal()
        );
    }

    public function testTemplateIsNotRenderedWhenNoDocumentIsPresent()
    {
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(Document::class)->willReturn(null);
        $this->renderer->render()->shouldNotBeCalled();
        $req = $this->request->reveal();
        $this->delegate->process($req)->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process($req, $this->delegate->reveal());

    }

    public function testTemplateIsRendered()
    {
        $doc = $this->prophesize(Document::class)->reveal();
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(Document::class)->willReturn($doc);
        $this->renderer->render('SomeTemplate', Argument::type('array'))->willReturn('Foo');

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

}
