<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Prismic;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\View\HelperPluginManager;

class ExperimentInitiator implements MiddlewareInterface
{

    public const START_EXPERIMENT_JS = 'PrismicToolbar.startExperiment("%1$s");';

    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var HelperPluginManager
     */
    private $helpers;

    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var string
     */
    private $toolbarScript;

    /**
     * @var string
     */
    private $endpointScript;

    public function __construct(
        Prismic\Api $api,
        HelperPluginManager $helpers,
        string $toolbarScript,
        string $endpointScript,
        string $apiEndpoint
    ) {
        $this->api = $api;
        $this->helpers = $helpers;
        $this->apiEndpoint = $apiEndpoint;
        $this->toolbarScript = $toolbarScript;
        $this->endpointScript = $endpointScript;
    }

    public function process(Request $request, RequestHandlerInterface $delegate) : Response
    {
        /**
         * Prismic is only capable of one experiment at a time
         */
        $experiments = $this->api->getExperiments();
        $experiment  = $experiments ? $experiments->getCurrent() : null;
        if (! $experiment) {
            /**
             * If no experiment is running, check for and expire the experiment cookie if it is present in the request
             */
            $response = $delegate->handle($request);
            $experimentCookie = FigRequestCookies::get($request, Prismic\Api::EXPERIMENTS_COOKIE);
            if ($experimentCookie->getValue() !== null) {
                $response = FigResponseCookies::expire($response, Prismic\Api::EXPERIMENTS_COOKIE);
            }
            return $response;
        }

        $helper = $this->helpers->get('inlineScript');

        /**
         * Inject API Endpoint into an object first so that it is available to prismic.min.js
         */
        $helper->appendScript(sprintf($this->endpointScript, $this->apiEndpoint));

        /**
         * Inject prismic.min.js
         */
        $helper->appendFile($this->toolbarScript);

        /**
         * Call the startExperiment method on global prismic object
         */
        $helper->appendScript(sprintf(
            self::START_EXPERIMENT_JS,
            $experiment->getGoogleId()
        ));

        return $delegate->handle($request);
    }
}
