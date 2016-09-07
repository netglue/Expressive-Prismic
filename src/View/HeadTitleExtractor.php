<?php
declare(strict_types=1);

namespace ExpressivePrismic\View;
use Prismic;

class HeadTitleExtractor extends AbstractExtractor implements ExtractorInterface
{

    /**
     * Document Properties to search for a head title
     * @var array
     */
    private $search = [];

    /**
     * Construct with array of document props to search for a head title.
     * Search is FIFO so only 1 string will be returned when multiple properties are used.
     * @param array $search
     */
    public function __construct(array $search = [])
    {
        foreach ($search as $index => $property) {
            if (!is_string($property)) {
                throw new \InvalidArgumentException(sprintf(
                    'Properties to search for the head title must be string values, recieved %s at index %s',
                    gettype($property),
                    $index
                ));
            }
            $this->search = $search;
        }
    }

    /**
     * To conform with the interface, this method returns an array with the single element ['title' => 'value']
     *
     * @param Prismic\WithFragments $document You can provide a Prismic\GroupDoc, but if you do, $type is mandatory
     * @param string $type Not required with a Prismic\Document instance
     * @return array
     * @throws \InvalidArgumentException if type is not supplied and $document is not a Document instance
     */
    public function extract(Prismic\WithFragments $document, string $type = null) : array
    {
        $type = $this->getType($document, $type);

        foreach($this->search as $property) {
            if ($value = $this->getText($document, $property, $type)) {
                return ['title' => $value];
            }
        }

        return [];
    }


}
