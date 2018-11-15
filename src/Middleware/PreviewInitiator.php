<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use ExpressivePrismic\Exception\RuntimeException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Prismic;
use Prismic\Exception as PrismicException;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class PreviewInitiator implements MiddlewareInterface
{

    /**
     * @var Prismic\Api
     */
    private $api;

    public function __construct(Prismic\Api $api)
    {
        $this->api = $api;
    }

    public function process(Request $request, DelegateInterface $delegate) : Response
    {
        $query = $request->getQueryParams();
        if (! isset($query['token']) || empty($query['token'])) {
            // Pass through in order to raise a 404
            return $delegate->handle($request);
        }

        $token = urldecode($query['token']);

        /**
         * Figure out URL and redirect
         */
        try {
            $url = $this->api->previewSession($token, '/');
            return new RedirectResponse($url, 302);
        } catch (PrismicException\ExceptionInterface $exception) {
            /**
             * If possible return a more friendly error message for the relatively common occurrence that a
             * preview token has expired
             */
            if ($exception instanceof PrismicException\ExpiredPreviewTokenException) {
                return $this->generatePreviewExpiredError();
            }
            throw new RuntimeException(
                'An unknown error has occurred',
                500,
                $exception
            );
        }
    }

    private function generatePreviewExpiredError() : Response
    {
        $responseBody = <<<EOF
<html>
<head><title>Error: Preview Expired</title></head>
<body>
<h1>Preview Token Expired</h1>
<p>This error occurs when you re-use an out of date link to preview content.</p>
<p>Simply <a href="/">navigate away</a> from this error, or start a fresh preview session from within the CMS.</p>
</body>
</html>
EOF;
        return new HtmlResponse($responseBody, 410);
    }
}
