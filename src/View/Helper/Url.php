<?php
declare(strict_types = 1);
namespace ExpressivePrismic\View\Helper;

use Prismic;
use Prismic\Document;
use Prismic\Fragment\Link\LinkInterface;

/**
 * Prismic Document/Link Url View Helper
 *
 * @package ExpressivePrismic\View\Helper
 */
class Url
{
    /**
     * @var Prismic\Api
     */
    private $api;

    /**
     * @var Prismic\LinkResolver
     */
    private $resolver;

    /**
     * @param Prismic\Api          $api
     * @param Prismic\LinkResolver $resolver
     */
    public function __construct(Prismic\Api $api, Prismic\LinkResolver $resolver)
    {
        $this->resolver = $resolver;
        $this->api = $api;
    }

    /**
     * Resolve $target to a link
     *
     * There are a few ways target could be resolved:
     * When $target is a string, it is assumed to be a document id, so the
     * document is located and we try to resolve it to an URL
     *
     * $target can also be a document, or a Prismic\LinkInterface instance
     *
     * @param string|Document|LinkInterface $target
     * @return string|null
     */
    public function __invoke($target)
    {
        if (is_string($target)) {
            $target = $this->api->getByID($target);
        }

        if ($target instanceof Document) {
            return $this->resolver->resolveDocument($target);
        }

        if ($target instanceof LinkInterface) {
            return $this->resolver->resolve($target);
        }

        return null;
    }


}
