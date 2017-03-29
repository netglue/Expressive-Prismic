<?php
namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response;


class DelegateMock implements DelegateInterface
{
    public $request;

    public function process(ServerRequestInterface $request)
    {
        $this->request = $request;
        return new Response\TextResponse('foo');
    }
}
