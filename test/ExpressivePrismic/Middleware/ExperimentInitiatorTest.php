<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\SetCookies;
use ExpressivePrismic\Middleware\ExperimentInitiator;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\View\Helper\InlineScript;
use Zend\View\HelperPluginManager;

class ExperimentInitiatorTest extends TestCase
{

    /** @var Prismic\Api|ObjectProphecy */
    private $api;

    /** @var Prismic\Experiments|ObjectProphecy */
    private $experiments;

    /** @var HelperPluginManager|ObjectProphecy */
    private $helpers;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $delegate;

    /** @var Request|ObjectProphecy */
    private $request;

    public function setUp()
    {
        $this->api         = $this->prophesize(Prismic\Api::class);
        $this->experiments = $this->prophesize(Prismic\Experiments::class);
        $this->helpers     = $this->prophesize(HelperPluginManager::class);
        $this->delegate    = $this->prophesize(RequestHandlerInterface::class);
        $this->request     = $this->prophesize(Request::class);
    }

    public function getMiddleware(string $expectedSecret = 'foo') : ExperimentInitiator
    {
        return new ExperimentInitiator(
            $this->api->reveal(),
            $this->helpers->reveal(),
            '//Some-Url-Of-Remote-JS',
            '{SOME-JS WITH REPLACEMENT OF API URL: %s}',
            'THE_API_URL'
        );
    }

    public function testMiddlewareIsNoopWhenNoExperimentsAreRunning() : void
    {
        $this->api->getExperiments()->willReturn($this->experiments->reveal());
        $this->helpers->get('inlineScript')->shouldNotBeCalled();
        $this->request->getHeaderLine('Cookie')->willReturn('');
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testScriptsAreAdded()
    {
        $experiments = $this->prophesize(Prismic\Experiments::class);
        $experiment  = $this->prophesize(Prismic\Experiment::class);
        $experiment->getGoogleId()->willReturn('GOOGLE_ID');
        $experiments->getCurrent()->willReturn($experiment->reveal());
        $this->api->getExperiments()->willReturn($experiments->reveal());

        // The API URL should be set in JS
        $helper = $this->prophesize(InlineScriptStubForExperiments::class);
        $expect = sprintf('{SOME-JS WITH REPLACEMENT OF API URL: %s}', 'THE_API_URL');
        $helper->appendScript($expect)->shouldBeCalled();

        // The Prismic JS Source File should be set
        $helper->appendFile('//Some-Url-Of-Remote-JS')->shouldBeCalled();

        // The Experiment Initiation script should be injected into the helper
        $expect = sprintf(ExperimentInitiator::START_EXPERIMENT_JS, 'GOOGLE_ID');
        $helper->appendScript($expect)->shouldBeCalled();

        $this->helpers->get('inlineScript')->willReturn($helper->reveal());
        $request = $this->request->reveal();
        $this->delegate->handle($request)->shouldBeCalled();
        $middleware = $this->getMiddleware();
        $middleware->process($request, $this->delegate->reveal());
    }

    public function testThatExperimentCookiesAreExpiredWhenPresentAndNoExperimentIsRunning() : void
    {
        $request = new \Zend\Diactoros\ServerRequest();
        $request = FigRequestCookies::set($request, Cookie::create(Prismic\Api::EXPERIMENTS_COOKIE, 'whatever'));
        $this->api->getExperiments()->willReturn($this->experiments->reveal());
        $response = new Response();
        $this->delegate->handle($request)->willReturn($response);
        $middleware = $this->getMiddleware();
        $returnedResponse = $middleware->process($request, $this->delegate->reveal());
        $cookieList = SetCookies::fromResponse($returnedResponse);
        $this->assertTrue($cookieList->has(Prismic\Api::EXPERIMENTS_COOKIE));
        $cookie = $cookieList->get(Prismic\Api::EXPERIMENTS_COOKIE);
        $this->assertLessThan(time(), $cookie->getExpires());
    }
}

// InlineScript uses __call which Prophecy doesn't like
class InlineScriptStubForExperiments extends InlineScript
{
    public function appendScript($arg)
    {
    }
    public function appendFile($arg)
    {
    }
}
