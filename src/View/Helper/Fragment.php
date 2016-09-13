<?php

namespace ExpressivePrismic\View\Helper;

use Prismic;
use ExpressivePrismic\Service\CurrentDocument;

class Fragment
{

    /**
     * @var CurrentDocument
     */
    private $documentRegistry;

    /**
     * @var Prismic\LinkResolver
     */
    private $linkResolver;

    public function __construct(CurrentDocument $documentRegistry, Prismic\LinkResolver $linkResolver)
    {
        $this->documentRegistry = $documentRegistry;
        $this->linkResolver     = $linkResolver;
    }

    public function __invoke() : Fragment
    {
        return $this;
    }

    /**
     * @return Prismic\Fragment\FragmentInterface|null
     */
    public function get($name)
    {
        return $this->requireDocument()->get($this->name($name));
    }

    public function asHtml($name)
    {
        if ($frag = $this->get($name)) {
            return $frag->asHtml($this->linkResolver);
        }
    }

    public function asText($name)
    {
        if ($frag = $this->get($name)) {
            return $frag->asText();
        }
    }

    /**
     * Get Document
     * @return Prismic\Document|null
     */
    public function getDocument()
    {
        return $this->documentRegistry->getDocument();
    }

    /**
     * Return Document
     * @return Prismic\Document
     */
    public function requireDocument() : Prismic\Document
    {
        $d = $this->getDocument();
        if (!$d) {
            throw new \RuntimeException('No prismic document has been set in the document registry');
        }
        return $d;
    }

    /**
     * Normalise a fragment name to include the document type
     * @param string $name
     * @return string
     */
    private function name(string $name) : string
    {
        $type = $this->requireDocument()->getType();
        if (strpos($name, $type) === 0) {
            return $name;
        }
        if (strpos($name, '.') !== false) {
            throw new \RuntimeException(sprintf(
                'Found a dot in the fragment name [%s] but does not match configured mask/type of %s',
                $name,
                $type
            ));
        }
        return sprintf('%s.%s', $type, $name);
    }

}
