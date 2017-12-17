<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\Middleware\ApiCacheBust;

// Deps
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;
use Prismic;

class ApiCacheBustTest extends TestCase
{

    private $cache;
    private $api;
    private $delegate;
    private $request;

    public function setUp()
    {
        $this->cache    = $this->prophesize(Prismic\Cache\CacheInterface::class);
        $this->api      = $this->prophesize(Prismic\Api::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware(string $expectedSecret = 'foo')
    {
        return new ApiCacheBust(
            $this->api->reveal(),
            $expectedSecret
        );
    }

    public function testEmptyRequestBodyIsError()
    {
        $this->request->getBody()->willReturn(null);

        $middleware = $this->getMiddleware();

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertJsonResponseIsError($response, 400);
    }

    private function assertJsonResponseIsError($response, $expectedCode = 400)
    {
        $this->assertSame($expectedCode, $response->getStatusCode());
        $json = json_decode((string)$response->getBody(), true);
        $this->assertTrue($json['error']);
        $this->assertInternalType('string', $json['message']);
    }

    private function assertJsonResponseIsSuccess($response)
    {
        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode((string)$response->getBody(), true);
        $this->assertFalse($json['error']);
        $this->assertInternalType('string', $json['message']);
    }

    public function testInvalidJsonIsError()
    {
        $this->request->getBody()->willReturn('foo');
        $middleware = $this->getMiddleware();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertJsonResponseIsError($response, 400);
    }

    public function testMissingSecretIsError()
    {
        $this->request->getBody()->willReturn('{"json" : "foo"}');
        $middleware = $this->getMiddleware();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testIncorrectSecretIsError()
    {
        $this->request->getBody()->willReturn('{"secret" : "wrong"}');
        $middleware = $this->getMiddleware();
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testCorrectSecretIsSuccess()
    {
        $this->request->getBody()->willReturn('{"secret" : "wrong"}');
        $middleware = $this->getMiddleware('wrong');
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsSuccess($response);
    }

    public function testCacheIsCleanedWithApiUpdate()
    {
        $this->cache->clear()->shouldBeCalled();
        $this->api->getCache()->willReturn($this->cache->reveal());

        $this->request->getBody()->willReturn('{"secret" : "wrong", "type":"api-update"}');
        $middleware = $this->getMiddleware('wrong');
        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
        $this->assertJsonResponseIsSuccess($response);

    }



}

