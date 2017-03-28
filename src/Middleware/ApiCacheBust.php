<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;
use Prismic;

/**
 * Middleware for busting the Prismic cache in receipt of a webhook
 *
 * @package ExpressivePrismic\Middleware
 */
class ApiCacheBust implements MiddlewareInterface
{
    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var string
     */
    private $expectedSecret;

    /**
     * ApiCacheBust constructor.
     *
     * @param Prismic\Api $api
     */
    public function __construct(Prismic\Api $api, string $expectedSecret)
    {
        $this->api = $api;
        $this->expectedSecret = $expectedSecret;
    }

    /**
     * @param  Request           $request
     * @param  DelegateInterface $delegate
     * @return Response
     */
    public function process(Request $request, DelegateInterface $delegate)
    {
        $body = (string) $request->getBody();

        if (empty($body)) {
            return $this->jsonError('Bad Request', 400);
        }

        $json = json_decode($body, true, 10);

        if (!$json) {
            return $this->jsonError('Invalid payload', 400);
        }


        if (!isset($json['secret']) || $json['secret'] !== $this->expectedSecret) {
            return $this->jsonError('Invalid payload', 400);
        }

        if (isset($json['type']) && $json['type'] === 'api-update') {
            if ($cache = $this->api->getCache()) {
                $cache->clear();
            }
        }

        $data = [
            'error' => false,
            'message' => 'Payload Received',
        ];

        return new JsonResponse($data, 200);
    }

    /**
     * Return a JSON Response in error conditions with the given message and status code
     * @param string $message
     * @param int    $code
     * @return JsonResponse
     */
    private function jsonError(string $message, int $code) : JsonResponse
    {
        $data = [
            'error' => true,
            'message' => $message,
        ];

        return new JsonResponse($data, $code);
    }
}
