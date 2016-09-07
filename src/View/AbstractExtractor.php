<?php
declare(strict_types=1);

namespace ExpressivePrismic\View;
use Prismic;

abstract class AbstractExtractor
{

    public function getType(Prismic\WithFragments $document, string $type = null) : string
    {
        if (!$document instanceof Prismic\Document && empty($type)) {
            throw new \InvalidArgumentException('You must provide the document type as the second parameter if you are not providing a Document instance');
        }

        return $document instanceof Prismic\Document ? $document->getType() : $type;
    }

    public function getText(Prismic\WithFragments $document, string $fragmentName, string $type = null)
    {
        $type = is_null($type) ? $this->getType($document) : $type;
        $fragment = $document->get(sprintf('%s.%s', $type, $fragmentName));
        $value = null;
        if ($fragment) {
            $data = $fragment->asText();
            $value = empty($data) ? $value : $data;
        }
        return $value;
    }


}
