<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use function method_exists;
use const PHP_SAPI;
use Prismic\Api;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CliCacheBust implements MiddlewareInterface
{

    /** @var Api */
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute(ValidatePrismicWebhook::class);
        $isWebhookCacheBust = $data && isset($data['type']) && $data['type'] === 'api-update';
        $isCliCacheBust = PHP_SAPI === 'cli' && method_exists($this->api, 'reloadApiData');
        if ($isWebhookCacheBust && $isCliCacheBust) {
            $this->api->reloadApiData();
        }
        return $handler->handle($request);
    }
}
