<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Exception\DocumentNotFoundException;
use ExpressivePrismic\Middleware\PrismicTemplate;
use ExpressivePrismicTest\TestCase;
use Prismic\DocumentInterface;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class PrismicTemplateTest extends TestCase
{

    private $delegate;
    private $request;
    private $renderer;

    public function setUp() : void
    {
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
        $this->renderer  = $this->prophesize(TemplateRendererInterface::class);
    }

    private function getMiddleware() : PrismicTemplate
    {
        return new PrismicTemplate(
            $this->renderer->reveal()
        );
    }

    public function testExceptionIsThrownWhenDocumentCannotBeResolved() : void
    {
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(DocumentInterface::class)->willReturn(null);
        $middleware = $this->getMiddleware();
        $this->expectException(DocumentNotFoundException::class);
        $middleware->process($this->request->reveal(), $this->delegate->reveal());
    }

    public function testTemplateIsRendered() : void
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
