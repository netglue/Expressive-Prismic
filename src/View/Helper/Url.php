<?php
declare(strict_types = 1);
namespace ExpressivePrismic\View\Helper;

use Prismic;
use Prismic\DocumentInterface;
use Prismic\Document\Fragment\LinkInterface;

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
     * @param Prismic\Api          $api
     */
    public function __construct(Prismic\Api $api)
    {
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
     * @param string|DocumentInterface|LinkInterface $target
     * @return string|null
     */
    public function __invoke($target)
    {
        if (is_string($target)) {
            $target = $this->api->getById($target);
        }

        if ($target instanceof DocumentInterface) {
            $target = $target->asLink();
        }

        if ($target instanceof LinkInterface) {
            return $target->getUrl();
        }

        return null;
    }
}
