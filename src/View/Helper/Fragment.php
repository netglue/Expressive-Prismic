<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper;

use Prismic;
use ExpressivePrismic\Service\CurrentDocument;
use ExpressivePrismic\Exception;
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
     * @var Prismic\LinkResolver
     */
    private $linkResolver;

    /**
     * Fragment constructor.
     *
     * @param CurrentDocument      $documentRegistry
     * @param Prismic\LinkResolver $linkResolver
     */
    public function __construct(CurrentDocument $documentRegistry, Prismic\LinkResolver $linkResolver)
    {
        $this->documentRegistry = $documentRegistry;
        $this->linkResolver     = $linkResolver;
    }

    /**
     * @return self
     */
    public function __invoke() : Fragment
    {
        return $this;
    }

    /**
     * @param  string $name
     * @return Prismic\Fragment\FragmentInterface|null
     */
    public function get(string $name) :? Prismic\Fragment\FragmentInterface
    {
        return $this->requireDocument()->get($this->name($name));
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function asHtml(string $name) :? string
    {
        if ($frag = $this->get($name)) {
            return $frag->asHtml($this->linkResolver);
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
     * @return Prismic\Document|null
     */
    public function getDocument() :? Prismic\Document
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
            throw new Exception\RuntimeException('No prismic document has been set in the document registry');
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
            throw new Exception\RuntimeException(sprintf(
                'Found a dot in the fragment name [%s] but does not match configured mask/type of %s',
                $name,
                $type
            ));
        }

        return sprintf('%s.%s', $type, $name);
    }

}
