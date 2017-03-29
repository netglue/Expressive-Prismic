<?php

namespace ExpressivePrismic\Middleware;

use Prismic;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Request;

class ApiCacheBustTest extends \PHPUnit_Framework_TestCase
{

    private $secret = 'secret';
    private $api;
    private $middleware;
    private $delegate;

    public function setUp()
    {
        $this->api = $this->createMock(Prismic\Api::class);
        $this->api->method('getCache')->willReturn(Prismic\Api::defaultCache());
        $this->middleware = new ApiCacheBust($this->api, $this->secret);
        $this->delegate = new DelegateMock;
    }

    public function testEmptyRequestBodyIsError()
    {
        $response = $this->middleware->process(new ServerRequest, $this->delegate);
        $this->assertSame(400, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertTrue($json['error']);
        $this->assertInternalType('string', $json['message']);
    }

    private function requestWithBody(string $body)
    {
        $request = new ServerRequest([], [], '/', 'POST', 'php://memory');
        $request->getBody()->write($body);
        return $request;
    }

    private function assertJsonResponseIsError($response, $expectedCode = 400)
    {
        $this->assertSame($expectedCode, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertTrue($json['error']);
        $this->assertInternalType('string', $json['message']);
    }

    private function assertJsonResponseIsSuccess($response)
    {
        $this->assertSame(200, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertFalse($json['error']);
        $this->assertInternalType('string', $json['message']);
    }

    public function testInvalidJsonIsError()
    {
        $response = $this->middleware->process($this->requestWithBody('Some Text'), $this->delegate);
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testMissingSecretIsError()
    {
        $response = $this->middleware->process($this->requestWithBody('{"json" : "foo"}'), $this->delegate);
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testIncorrectSecretIsError()
    {
        $response = $this->middleware->process($this->requestWithBody('{"secret" : "wrong"}'), $this->delegate);
        $this->assertJsonResponseIsError($response, 400);
    }

    public function testCorrectSecretIsSuccess()
    {
        $response = $this->middleware->process($this->requestWithBody('{"secret" : "secret"}'), $this->delegate);
        $this->assertJsonResponseIsSuccess($response);
    }

    public function testCacheIsCleanedWithApiUpdate()
    {
        $cache = $this->api->getCache();
        $cache->set('foo', 'foo');
        $this->assertTrue($cache->has('foo'));

        $body = json_encode([
            'secret' => $this->secret,
            'type' => 'api-update'
        ]);

        $response = $this->middleware->process($this->requestWithBody($body), $this->delegate);
        $this->assertJsonResponseIsSuccess($response);

        $this->assertFalse($cache->has('foo'));

    }



}

