<?php
declare(strict_types=1);

namespace ExpressivePrismic\View;

class TwitterCardExtractor extends MetaDataExtractor
{

    /**
     * Acceptable meta types
     * @var array
     */
    protected $accept = [
        'twitter:card',
        'twitter:site',
        'twitter:creator',
        'twitter:title',
        'twitter:description',
        'twitter:image',
        'twitter:image:alt',
        'twitter:player',
        'twitter:player:width',
        'twitter:player:height',
        'twitter:player:stream',
        'twitter:app:name:iphone',
        'twitter:app:id:iphone',
        'twitter:app:url:iphone',
        'twitter:app:name:ipad',
        'twitter:app:id:ipad',
        'twitter:app:url:ipad',
        'twitter:app:name:googleplay',
        'twitter:app:id:googleplay',
        'twitter:app:url:googleplay',
    ];


}
