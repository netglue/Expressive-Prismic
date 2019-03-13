<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use ExpressivePrismic\Middleware;

class PipelineAndRoutesDelegator
{
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback) : Application
    {
        /** @var Application $app */
        $app = $callback();

        $config = $container->get('config')['prismic'];

        /**
         * Preview Initiator
         */
        $app->route(
            $config['preview_url'],
            [Middleware\PreviewInitiator::class],
            ['GET'],
            'prismic-preview'
        );

        /**
         * Webhook Cache Bust
         */
        $app->route(
            $config['webhook_url'],
            [Middleware\WebhookPipe::class],
            ['POST'],
            'prismic-webhook-cache-bust'
        );

        return $app;
    }
}
