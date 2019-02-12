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

    private function getMiddleware() : ValidatePrismicWebhook
    {
        return new ValidatePrismicWebhook(
            'big-secret'
        );
    }

    public function testEmptyRequestBodyIsError() : void
    {
        $this->request->getBody()->willReturn(null);

        $middleware = $this->getMiddleware();

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
        $this->assertInternalType('string', $json['message']);
    }

    public function testInvalidJsonIsError() : void
    {
        $this->request->getBody()->willReturn('foo');
        $middleware = $this->getMiddleware();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertJsonResponseIsError($response, 400);
    }

    public function testMissingSecretIsError() : void
    {
        $this->request->getBody()->willReturn('{"json" : "foo"}');
        $middleware = $this->getMiddleware();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testIncorrectSecretIsError() : void
    {
        $this->request->getBody()->willReturn('{"secret" : "wrong"}');
        $middleware = $this->getMiddleware();
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
        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }
}
