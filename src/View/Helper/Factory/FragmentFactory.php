<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Prismic;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismic\Service\CurrentDocument;

class FragmentFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Fragment
    {
        $documentRegistry = $container->get(CurrentDocument::class);
        $resolver         = $container->get(Prismic\LinkResolver::class);
        return new Fragment($documentRegistry, $resolver);
    }

}
