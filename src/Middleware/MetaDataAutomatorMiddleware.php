<?php
namespace ExpressivePrismic\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use ExpressivePrismic\Service\MetaDataAutomator;

class MetaDataAutomatorMiddleware
{

    /**
     * @var MetaDataAutomator
     */
    private $automator;

    public function __construct(MetaDataAutomator $automator)
    {
        $this->automator = $automator;
    }

    public function __invoke(Request $request, Response $response, callable $next = null) : Response
    {
        if ($document = $request->getAttribute(Prismic\Document::class)) {
            $this->automator->apply($document);
        }

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }


}
