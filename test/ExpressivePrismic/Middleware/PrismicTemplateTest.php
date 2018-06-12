<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismic\Exception\DocumentNotFoundException;
use ExpressivePrismicTest\TestCase;
use Prismic\DocumentInterface;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\PrismicTemplate;

// Deps
use Zend\Expressive\Template\TemplateRendererInterface;
use Prismic\LinkResolver;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;

class PrismicTemplateTest extends TestCase
{

    private $delegate;
    private $request;
    private $renderer;

    public function setUp()
    {
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
        $this->renderer  = $this->prophesize(TemplateRendererInterface::class);
    }

    public function getMiddleware()
    {
        return new PrismicTemplate(
            $this->renderer->reveal()
        );
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\DocumentNotFoundException
     */
    public function testExceptionIsThrownWhenDocumentCannotBeResolved()
    {
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(DocumentInterface::class)->willReturn(null);
        $middleware = $this->getMiddleware();
        $middleware->process($this->request->reveal(), $this->delegate->reveal());
    }

    public function testTemplateIsRendered()
    {
        $doc = $this->prophesize(DocumentInterface::class)->reveal();
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(DocumentInterface::class)->willReturn($doc);
        $this->renderer->render('SomeTemplate', Argument::type('array'))->willReturn('Foo');

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }
}
