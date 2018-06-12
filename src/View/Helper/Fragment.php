<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper;

use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\DocumentInterface;

/**
 * Fragment View Helper
 *
 * @package ExpressivePrismic\View\Helper
 */
class Fragment
{

    /**
     * @var CurrentDocument
     */
    private $documentRegistry;

    /**
     * Fragment constructor.
     *
     * @param CurrentDocument $documentRegistry
     */
    public function __construct(CurrentDocument $documentRegistry)
    {
        $this->documentRegistry = $documentRegistry;
    }

    /**
     * @return self
     */
    public function __invoke() : self
    {
        return $this;
    }

    /**
     * @param  string $name
     * @return FragmentInterface|null
     */
    public function get(string $name) :? FragmentInterface
    {
        return $this->requireDocument()->get($name);
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function asHtml(string $name) :? string
    {
        if ($frag = $this->get($name)) {
            return $frag->asHtml();
        }

        return null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function asText(string $name) :? string
    {
        if ($frag = $this->get($name)) {
            return $frag->asText();
        }

        return null;
    }

    /**
     * Get Document
     * @return DocumentInterface|null
     */
    public function getDocument() :? DocumentInterface
    {
        return $this->documentRegistry->getDocument();
    }

    /**
     * Return Document
     * @return DocumentInterface
     */
    public function requireDocument() : DocumentInterface
    {
        $d = $this->getDocument();
        if (! $d) {
            throw new Exception\RuntimeException('No prismic document has been set in the document registry');
        }

        return $d;
    }
}
