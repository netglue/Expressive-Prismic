<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Service;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Service\RouteParams;

class RouteParamsTest extends TestCase
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
