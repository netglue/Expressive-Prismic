<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Middleware\ApiCacheBust;

// Deps
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Cache\CacheItemPoolInterface;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;

class ApiCacheBustTest extends TestCase
{

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    private $delegate;
    private $request;

    public function setUp()
    {
        $this->cache    = $this->prophesize(CacheItemPoolInterface::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    public function getMiddleware()
    {
        return new ApiCacheBust(
            $this->cache->reveal()
        );
    }



    public function testCacheIsCleanedWithApiUpdate()
    {
        $this->cache->clear()->shouldBeCalled();

        $this->request->getAttribute(ValidatePrismicWebhook::class)->willReturn([
            'type' => 'api-update',
        ]);

        $this->delegate->handle($this->request->reveal())->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }

    public function testCacheIsNotCleanedWithoutApiUpdate()
    {
        $this->cache->clear()->shouldNotBeCalled();

        $this->request->getAttribute(ValidatePrismicWebhook::class)->willReturn([
            'type' => 'other-update',
        ]);

        $this->delegate->handle($this->request->reveal())->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }

    public function testNoopWithoutValidationAttribute()
    {
        $this->cache->clear()->shouldNotBeCalled();
        $this->request->getAttribute(ValidatePrismicWebhook::class)->willReturn(null);
        $this->delegate->handle($this->request->reveal())->shouldBeCalled();

        $middleware = $this->getMiddleware();
        $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );
    }
}
