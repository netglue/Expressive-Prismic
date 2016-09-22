<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;
use Prismic;

/**
 * Middleware for busting the Prismic cache in receipt of a webhook
 *
 * @package ExpressivePrismic\Middleware
 */
class ApiCacheBust
{
    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * ApiCacheBust constructor.
     *
     * @param Prismic\Api $api
     */
    public function __construct(Prismic\Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|null $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {

        $body = (string) $request->getBody();

        if (empty($body)) {
            return $this->jsonError('Bad Request', 400);
        }

        $json = json_decode($body, true, 10);

        if (!$json) {
            return $this->jsonError('Invalid payload', 400);
        }

        $expectedSecret = $request->getAttribute('expectedSecret');

        if (!isset($json['secret']) || $json['secret'] !== $expectedSecret) {
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
