<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\Middleware\ValidatePrismicWebhook;

class ValidatePrismicWebhookFactory
{

    public function __invoke(ContainerInterface $container) : ValidatePrismicWebhook
    {
        $config = $container->get('config');
        $secret = isset($config['prismic']['webhook_secret']) ? $config['prismic']['webhook_secret'] : null;
        return new ValidatePrismicWebhook($secret);
    }
}
