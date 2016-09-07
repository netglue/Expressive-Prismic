<?php
declare(strict_types=1);

namespace ExpressivePrismic\View;

class OpenGraphExtractor extends MetaDataExtractor
{

    /**
     * Acceptable meta types
     * @var array
     */
    protected $accept = [];

    /**
     * Construct with map configuration
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $metaName => $property) {
            if (!preg_match('/^og|fb:/', $metaName)) {
                throw new \InvalidArgumentException(sprintf(
                    '%s is not a known opengraph meta tag',
                    $metaName
                ));
            }
            $this->map[$metaName] = $property;
        }
    }

}
