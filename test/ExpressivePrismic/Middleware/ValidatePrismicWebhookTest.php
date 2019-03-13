<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismicTest\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;

class ValidatePrismicWebhookTest extends TestCase
{

    private $delegate;
    private $request;

    public function setUp() : void
    {
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function testEmptyRequestBodyIsError() : void
    {
        $this->request->getBody()->willReturn(null);

        $middleware = new ValidatePrismicWebhook();

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertJsonResponseIsError($response, 400);
    }

    private function assertJsonResponseIsError($response, $expectedCode = 400) : void
    {
        $this->assertSame($expectedCode, $response->getStatusCode());
        $json = json_decode((string)$response->getBody(), true);
        $this->assertTrue($json['error']);
        $this->assertIsString($json['message']);
    }

    public function testInvalidJsonIsError() : void
    {
        $this->request->getBody()->willReturn('foo');
        $middleware = new ValidatePrismicWebhook();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertJsonResponseIsError($response, 400);
    }

    public function testMissingSecretIsError() : void
    {
        $this->request->getBody()->willReturn('{"json" : "foo"}');
        $middleware = new ValidatePrismicWebhook('need-a-secret');
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testIncorrectSecretIsError() : void
    {
        $this->request->getBody()->willReturn('{"secret" : "wrong"}');
        $middleware = new ValidatePrismicWebhook('right');
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testCorrectSecretIsSuccess() : void
    {
        $this->request->getBody()->willReturn('{"secret" : "big-secret"}');
        $this->request
            ->withAttribute(ValidatePrismicWebhook::class, ['secret' => 'big-secret'])
            ->willReturn($this->request->reveal());
        $this->delegate->handle($this->request->reveal())->shouldBeCalled();
        $middleware = new ValidatePrismicWebhook('big-secret');
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }

    public function testValidationSucceedsWhenNoSecretIsRequired() : void
    {
        $this->request->getBody()->willReturn('{"json" : "payload"}');
        $this->request
            ->withAttribute(ValidatePrismicWebhook::class, ['json' => 'payload'])
            ->willReturn($this->request->reveal());
        $this->delegate->handle($this->request->reveal())->shouldBeCalled();
        $middleware = new ValidatePrismicWebhook();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }
}
