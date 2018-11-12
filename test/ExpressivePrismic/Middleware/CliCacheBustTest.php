<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Middleware\CliCacheBust;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ExpressivePrismicTest\TestCase;
use Prismic\Api;

class CliCacheBustTest extends TestCase
{
    /** @var Api|ObjectProphecy */
    private $api;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    /** @var ResponseInterface */
    private $response;

    public function setUp()
    {
        parent::setUp();
        $this->api = $this->prophesize(Api::class);
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->handler->handle(Argument::type(ServerRequestInterface::class))->willReturn($this->response);
    }

    private function middleware() : CliCacheBust
    {
        return new CliCacheBust($this->api->reveal());
    }

    public function testIsNoopWhenWebhookNotValidated() : void
    {
        $this->api->reloadApiData()->shouldNotBeCalled();
        $this->request->getAttribute(ValidatePrismicWebhook::class)->willReturn(null);
        $middle = $this->middleware();
        $response = $middle->process($this->request->reveal(), $this->handler->reveal());
        $this->assertSame($this->response, $response);
    }

    public function testRefetchIsCalledWithValidPayload()
    {
        $this->request->getAttribute(ValidatePrismicWebhook::class)->willReturn([
            'type' => 'api-update',
        ]);
        $this->api->reloadApiData()->shouldBeCalled();
        $middle = $this->middleware();
        $response = $middle->process($this->request->reveal(), $this->handler->reveal());
        $this->assertSame($this->response, $response);
    }
}
