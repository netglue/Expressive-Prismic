<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Service;

use Prismic\Document;

/**
 * A registry of sorts to store what's considered to be the current document
 *
 * @package ExpressivePrismic\Service
 */
class CurrentDocument
{

    /**
     * @var Document|null
     */
    private $document;

    /**
     * Set the current document
     * @param Document $document
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Return the current document
     * @return Document|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Whether a document is set or not
     * @return bool
     */
    public function hasDocument()
    {
        return ($this->document instanceof Document);
    }

}
