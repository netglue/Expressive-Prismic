<?php
declare(strict_types = 1);

namespace ExpressivePrismic\Service;

use Zend\Stdlib\AbstractOptions;

/**
 * Effectively configuration for determining parameter names in routes
 *
 * @package ExpressivePrismic\Service
 */
class RouteParams extends AbstractOptions
{

    /**
     * @var string
     */
    private $bookmark = 'prismic-bookmark';

    /**
     * @var string
     */
    private $id       = 'prismic-id';

    /**
     * @var string
     */
    private $uid      = 'prismic-uid';

    /**
     * @var string
     */
    private $type     = 'prismic-type';

    /**
     * @param string $bookmark
     */
    public function setBookmark(string $bookmark)
    {
        $this->bookmark = $bookmark;
    }

    /**
     * @return string
     */
    public function getBookmark() : string
    {
        return $this->bookmark;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getUid() : string
    {
        return $this->uid;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
}
