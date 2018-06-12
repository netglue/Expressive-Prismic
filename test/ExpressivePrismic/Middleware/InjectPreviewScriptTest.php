<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\InjectPreviewScript;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\View\Helper\InlineScript;
use Zend\View\HelperPluginManager;

class InjectPreviewScriptTest extends TestCase
{

    private $api;
    private $helpers;
    private $delegate;
    private $request;

    public function setUp()
    {
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->helpers  = $this->prophesize(HelperPluginManager::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware(bool $alwaysInject = false)
    {
        return new InjectPreviewScript(
            $this->api->reveal(),
            $this->helpers->reveal(),
            '//Some-Url-Of-Remote-JS',
            '{SOME-JS WITH REPLACEMENT OF API URL: %s}',
            'THE_API_URL',
            $alwaysInject
        );
    }

    public function testMiddlewareIsNoopWhenNotInPreviewMode()
    {
        $this->api->inPreview()->willReturn(false);
        $this->helpers->get('inlineScript')->shouldNotBeCalled();
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testScriptsAreAdded()
    {
        $this->api->inPreview()->willReturn(true);
        $helper = $this->prophesize(InlineScriptStubForInjectPreview::class);
        $expect = sprintf('{SOME-JS WITH REPLACEMENT OF API URL: %s}', 'THE_API_URL');
        $helper->appendScript($expect)->shouldBeCalled();
        $helper->appendFile('//Some-Url-Of-Remote-JS')->shouldBeCalled();
        $this->helpers->get('inlineScript')->willReturn($helper->reveal());
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testScriptIsAddedWhenAlwaysInjectIsTrue()
    {
        $this->api->inPreview()->willReturn(false);
        $helper = $this->prophesize(InlineScriptStubForInjectPreview::class);
        $helper->appendScript(Argument::any())->shouldBeCalled();
        $helper->appendFile(Argument::any())->shouldBeCalled();
        $this->helpers->get('inlineScript')->willReturn($helper->reveal());
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();
        $middleware = $this->getMiddleware(true);
        $middleware->process($request, $this->delegate->reveal());
    }
}

// InlineScript uses __call which Prophecy doesn't like
class InlineScriptStubForInjectPreview extends InlineScript
{
    public function appendScript($arg)
    {
    }
    public function appendFile($arg)
    {
    }
}
