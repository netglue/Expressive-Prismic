<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Factory;

use Interop\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Application;

/**
 * Class LinkResolverFactory
 *
 * @package ExpressivePrismic\Factory
 */
class LinkResolverFactory
{

    /**
     * @param ContainerInterface $container
     * @param string                $requestedName
     * @param array|null         $options
     * @return LinkResolver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : LinkResolver
    {
        $api       = $container->get(Prismic\Api::class);
        $params    = $container->get(RouteParams::class);
        $urlHelper = $container->get(UrlHelper::class);
        $app       = $container->get(Application::class);

        return new LinkResolver($api, $params, $urlHelper, $app);
    }
}
