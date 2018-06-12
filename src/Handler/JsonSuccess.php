<?php
declare(strict_types = 1);
namespace ExpressivePrismic\Handler;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class JsonSuccess implements RequestHandlerInterface, MiddlewareInterface
{

    public function handle(Request $request) : Response
    {
        $data = [
            'success' => true,
            'message' => 'Payload Received',
        ];

        return new JsonResponse($data, 200);
    }

    public function process(Request $request, RequestHandlerInterface $handler) : Response
    {
        return $this->handle($request);
    }
}
