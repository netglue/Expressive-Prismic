<?php
declare(strict_types=1);

namespace ExpressivePrismic\Middleware\Factory;

use Interop\Container\ContainerInterface;

use Zend\Expressive\Template\TemplateRendererInterface;
use ExpressivePrismic\Middleware\PrismicTemplate;
use ExpressivePrismic\LinkResolver;

class PrismicTemplateFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : PrismicTemplate
    {
        $renderer = $container->get(TemplateRendererInterface::class);
        $helpers = $container->get(\Zend\View\HelperPluginManager::class);
        $linkResolver = $container->get(LinkResolver::class);
        return new PrismicTemplate($renderer, $helpers, $linkResolver);
    }
}
