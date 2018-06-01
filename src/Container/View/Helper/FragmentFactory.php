<?php
declare(strict_types=1);

namespace ExpressivePrismic\Container\View\Helper;

use Psr\Container\ContainerInterface;
use ExpressivePrismic\View\Helper\Fragment;
use ExpressivePrismic\Service\CurrentDocument;

class FragmentFactory
{

    public function __invoke(ContainerInterface $container) : Fragment
    {
        return new Fragment(
            $container->get(CurrentDocument::class)
        );
    }
}
