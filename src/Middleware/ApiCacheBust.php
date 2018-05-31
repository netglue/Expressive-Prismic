<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Cache\CacheItemPoolInterface;

class ApiCacheBust implements MiddlewareInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function process(Request $request, DelegateInterface $delegate)
    {
        $data = $request->getAttribute(ValidatePrismicWebhook::class);
        if ($data && isset($data['type']) && $data['type'] === 'api-update') {
            $this->cache->clear();
        }
        return $delegate->process($request);
    }
}
