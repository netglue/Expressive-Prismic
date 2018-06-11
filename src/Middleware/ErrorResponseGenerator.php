<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Zend\Stratigility\MiddlewarePipeInterface;
use Zend\Stratigility\Utils;
use Zend\Diactoros\Response\TextResponse;

class ErrorResponseGenerator implements RequestHandlerInterface
{

    /**
     * @var MiddlewarePipeInterface
     */
    private $pipe;

    public function __construct(MiddlewarePipeInterface $pipe)
    {
        $this->pipe = $pipe;
    }

    public function __invoke(Throwable $error, Request $request, Response $response) : Response
    {
        try {
            $response = $this->pipe->process($request, $this);
            return $response->withStatus(Utils::getStatusCode($error, $response));
        } catch (\Throwable $e) {
            return $this->generateFallbackResponse();
        }
    }

    public function handle(Request $request) : Response
    {
        return $this->generateFallbackResponse();
    }

    private function generateFallbackResponse() : Response
    {
        return new TextResponse('An Unexpected Error Occurred', 500);
    }
}
