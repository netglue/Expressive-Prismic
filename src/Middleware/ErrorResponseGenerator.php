<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\DocumentNotFoundException;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Zend\Stratigility\MiddlewarePipeInterface;
use Zend\Diactoros\Response\TextResponse;

class ErrorResponseGenerator implements RequestHandlerInterface
{

    /**
     * @var MiddlewarePipeInterface
     */
    private $errorPipeline;

    /**
     * @var MiddlewarePipeInterface
     */
    private $notFoundPipeline;

    public function __construct(
        MiddlewarePipeInterface $errorPipeline,
        MiddlewarePipeInterface $notFoundPipeline
    ) {
        $this->errorPipeline = $errorPipeline;
        $this->notFoundPipeline = $notFoundPipeline;
    }

    public function __invoke(Throwable $error, Request $request, Response $response) : Response
    {
        try {
            if ($error instanceof DocumentNotFoundException) {
                $response = $this->notFoundPipeline->process($request, $this);
                return $response->withStatus(404);
            }
            $response = $this->errorPipeline->process($request, $this);
            return $response->withStatus(500);
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
