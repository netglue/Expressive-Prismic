<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Middleware\ExperimentInitiator;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Zend\View\Helper\InlineScript;
use Zend\View\HelperPluginManager;

class ExperimentInitiatorTest extends TestCase
{

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var Prismic\Experiments
     */
    private $experiments;

    private $helpers;
    private $delegate;
    private $request;

    public function setUp()
    {
        $this->api         = $this->prophesize(Prismic\Api::class);
        $this->experiments = $this->prophesize(Prismic\Experiments::class);
        $this->helpers     = $this->prophesize(HelperPluginManager::class);
        $this->delegate    = $this->prophesize(DelegateInterface::class);
        $this->request     = $this->prophesize(Request::class);
    }

    public function getMiddleware(string $expectedSecret = 'foo')
    {
        return new ExperimentInitiator(
            $this->api->reveal(),
            $this->helpers->reveal(),
            '//Some-Url-Of-Remote-JS',
            '{SOME-JS WITH REPLACEMENT OF API URL: %s}',
            'THE_API_URL'
        );
    }

    public function testMiddlewareIsNoopWhenNoExperimentsAreRunning()
    {
        $this->api->getExperiments()->willReturn($this->experiments->reveal());
        $this->helpers->get('inlineScript')->shouldNotBeCalled();
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
