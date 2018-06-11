<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\Handler;

use Psr\Container\ContainerInterface;

use Zend\Expressive\Template\TemplateRendererInterface;
use ExpressivePrismic\Handler\PrismicTemplate;

class PrismicTemplateFactory
{
    public function __invoke(ContainerInterface $container) : PrismicTemplate
    {
        return new PrismicTemplate(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
