<?php
declare(strict_types=1);

namespace ExpressivePrismic\View;
use Prismic;

class MetaDataExtractor extends AbstractExtractor implements ExtractorInterface
{

    /**
     * A map of meta types to document properties configured by the user
     * @var array
     */
    protected $map = [

    ];

    /**
     * Acceptable meta types
     * @var array
     */
    protected $accept = [
        'keywords',
        'description',
        'subject',
        'copyright',
        'language',
        'robots',
        'revised',
        'abstract',
        'topic',
        'summary',
        'Classification',
        'author',
        'designer',
        'reply-to',
        'owner',
        'directory',
        'pagename',
        'category',
        'coverage',
        'distribution',
        'rating',
        'revisit-after',
        'subtitle',
        'date',
        'search_date',
        'DC.title',
        'medium',
    ];

    /**
     * Construct with map configuration
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $metaName => $property) {
            if (!in_array($metaName, $this->accept)) {
                throw new \InvalidArgumentException(sprintf(
                    '%s is not a known HTML meta tag',
                    $metaName
                ));
            }
            $this->map[$metaName] = $property;
        }
    }

    /**
     * Return a hash of meta tag values that can iterated over to set meta tags for a page in bulk
     *
     * @param Prismic\WithFragments $document You can provide a Prismic\GroupDoc, but if you do, $type is mandatory
     * @param string $type Not required with a Prismic\Document instance
     * @return array
     * @throws \InvalidArgumentException if type is not supplied and $document is not a Document instance
     */
    public function extract(Prismic\WithFragments $document, string $type = null) : array
    {
        $type = $this->getType($document, $type);

        $return = [];

        foreach($this->map as $metaName => $property) {
            if ($value = $this->getText($document, $property, $type)) {
                $return[$metaName] = $value;
            }
        }

        return $return;
    }

}
