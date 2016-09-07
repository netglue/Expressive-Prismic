<?php
declare(strict_types=1);

namespace ExpressivePrismic\Service;

use Zend\Stdlib\AbstractOptions;

class RouteParams extends AbstractOptions
{

    private $bookmark = 'prismic-bookmark';
    private $id       = 'prismic-id';
    private $uid      = 'prismic-uid';
    private $type     = 'prismic-type';

    public function setBookmark(string $bookmark)
    {
        $this->bookmark = $bookmark;
    }

    public function getBookmark() : string
    {
        return $this->bookmark;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setUid(string $uid)
    {
        $this->uid = $uid;
    }

    public function getUid() : string
    {
        return $this->uid;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType() : string
    {
        return $this->type;
    }
}
