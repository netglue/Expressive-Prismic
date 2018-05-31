<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Service;

use Prismic\DocumentInterface;

/**
 * A registry of sorts to store what's considered to be the current document
 *
 * @package ExpressivePrismic\Service
 */
class CurrentDocument
{

    /**
     * @var DocumentInterface|null
     */
    private $document;

    /**
     * Set the current document
     * @param DocumentInterface $document
     */
    public function setDocument(DocumentInterface $document) : void
    {
        $this->document = $document;
    }

    /**
     * Return the current document
     * @return DocumentInterface|null
     */
    public function getDocument() :? DocumentInterface
    {
        return $this->document;
    }

    /**
     * Whether a document is set or not
     * @return bool
     */
    public function hasDocument() : bool
    {
        return ($this->document instanceof DocumentInterface);
    }
}
