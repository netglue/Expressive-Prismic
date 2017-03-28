<?php

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
    // Prismic Module Configuration
    ExpressivePrismic\ConfigProvider::class,
    new PhpFileProvider(__DIR__ . '/autoload/{{,*.}global,{,*.}local}.php'),
]);

return $aggregator->getMergedConfig();
