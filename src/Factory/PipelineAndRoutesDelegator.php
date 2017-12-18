<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use ExpressivePrismic\Middleware;

class PipelineAndRoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param string $serviceName Name of the service being created.
     * @param callable $callback Creates and returns the service.
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        /** @var $app Application */
        $app = $callback();

        /**
         * Preview Initiator
         */
        $app->route('/prismic-preview', [Middleware\PreviewInitiator::class], ['GET'], 'prismic-preview');

        /**
         * Webhook Cache Bust
         */
        $app->route('/prismicio-cache-webhook', [Middleware\WebhookMiddlewarePipe::class], ['POST'], 'prismic-webhook-cache-bust');

        return $app;
    }

}
