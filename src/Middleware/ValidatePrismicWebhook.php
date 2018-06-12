<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Middleware that returns an error response if the webhook post
 * is not valid, otherwise delegates to the next middleware in the queue after
 * setting the decoded, posted payload as a request attribute
 */

class ValidatePrismicWebhook implements MiddlewareInterface
{

    /**
     * @var string
     */
    private $expectedSecret;

    public function __construct(string $expectedSecret)
    {
        $this->expectedSecret = $expectedSecret;
    }

    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        $body = (string) $request->getBody();

        if (empty($body)) {
            return $this->jsonError('Bad Request', 400);
        }

        $json = json_decode($body, true, 10);

        if (! $json) {
            return $this->jsonError('Invalid payload', 400);
        }


        if (! isset($json['secret']) || $json['secret'] !== $this->expectedSecret) {
            return $this->jsonError('Invalid payload', 400);
        }

        $request = $request->withAttribute(__CLASS__, $json);

        return $delegate->handle($request);
    }

    /**
     * Return a JSON Response in error conditions with the given message and status code
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
