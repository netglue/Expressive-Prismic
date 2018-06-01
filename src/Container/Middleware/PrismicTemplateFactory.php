<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Middleware;

use Psr\Container\ContainerInterface;

use Zend\Expressive\Template\TemplateRendererInterface;
use ExpressivePrismic\Middleware\PrismicTemplate;
use Prismic\LinkResolver;

class PrismicTemplateFactory
{
    public function __invoke(ContainerInterface $container) : PrismicTemplate
    {
        return new PrismicTemplate(
            $container->get(TemplateRendererInterface::class),
            $container->get(LinkResolver::class)
        );
    }
}
