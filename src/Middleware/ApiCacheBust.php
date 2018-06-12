<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate) : ResponseInterface
    {
        $data = $request->getAttribute(ValidatePrismicWebhook::class);
        if ($data && isset($data['type']) && $data['type'] === 'api-update') {
            $this->cache->clear();
        }
        return $delegate->handle($request);
    }
}
