<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccess implements MiddlewareInterface
{

    public function process(Request $request, DelegateInterface $delegate)
    {
        $data = [
            'success' => true,
            'message' => 'Payload Received',
        ];

        return new JsonResponse($data, 200);
    }
}
