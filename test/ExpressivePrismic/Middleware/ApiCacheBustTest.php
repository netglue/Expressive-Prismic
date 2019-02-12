<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Middleware;

use ExpressivePrismic\Middleware\ApiCacheBust;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;
use ExpressivePrismicTest\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;

class ApiCacheBustTest extends TestCase
{

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    private $delegate;
    private $request;

    public function setUp() : void
    {
        $this->cache    = $this->prophesize(CacheItemPoolInterface::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->request  = $this->prophesize(Request::class);
    }

    private function getMiddleware() : ApiCacheBust
    {
        return new ApiCacheBust(
            $this->cache->reveal()
        );
    }

    public function testCacheIsCleanedWithApiUpdate() : void
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

    public function testCacheIsNotCleanedWithoutApiUpdate() : void
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

    public function testNoopWithoutValidationAttribute() : void
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
