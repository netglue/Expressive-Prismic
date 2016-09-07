<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Factory;

use Interop\Container\ContainerInterface;
use ExpressivePrismic\View\MetaDataExtractor;

class MetaDataExtractorFactory
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : MetaDataExtractor
    {
        $config = $container->get('config');
        $options = isset($config['prismic']['head']['meta_data_map'])
            ? $config['prismic']['head']['meta_data_map']
            : [];

        return new MetaDataExtractor($options);
    }
}
