<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Handler;

// Infra
use ExpressivePrismic\Exception\DocumentNotFoundException;
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Handler\PrismicTemplate;

// Deps
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\HtmlResponse;
use Prismic\DocumentInterface;

class PrismicTemplateTest extends TestCase
{

    private $request;
    private $renderer;

    public function setUp()
    {
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
        $middleware->handle($this->request->reveal());
    }

    public function testTemplateIsRendered()
    {
        $doc = $this->prophesize(DocumentInterface::class)->reveal();
        $this->request->getAttribute('template')->willReturn('SomeTemplate');
        $this->request->getAttribute(DocumentInterface::class)->willReturn($doc);
        $this->renderer->render('SomeTemplate', Argument::type('array'))->willReturn('Foo');

        $middleware = $this->getMiddleware();
        $response = $middleware->handle($this->request->reveal());
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }
}
