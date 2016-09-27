<?php
namespace ExpressivePrismic\Service;

use ExpressivePrismic\Service\RouteParams;

class RouteParamsTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructWithEmptyArrayRetainsDefaults()
    {
        $params = new RouteParams([]);
        $this->assertSame('prismic-bookmark', $params->getBookmark());
    }

    public function testConstructWithValuesReturnsExpected()
    {
        $params = new RouteParams([
            'bookmark' => 'foo',
            'uid' => 'bar',
            'id' => 'baz',
            'type' => 'bat',
        ]);

        $this->assertSame('foo', $params->getBookmark());
        $this->assertSame('bar', $params->getUid());
        $this->assertSame('baz', $params->getId());
        $this->assertSame('bat', $params->getType());
    }

}
