<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Service;

use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismicTest\TestCase;

class RouteParamsTest extends TestCase
{

    public function testConstructWithEmptyArrayRetainsDefaults() : void
    {
        $params = new RouteParams([]);
        $this->assertSame('prismic-bookmark', $params->getBookmark());
        $this->assertSame('prismic-uid', $params->getUid());
        $this->assertSame('prismic-id', $params->getId());
        $this->assertSame('prismic-type', $params->getType());
        $this->assertSame('prismic-lang', $params->getLang());
    }

    public function testConstructWithValuesReturnsExpected() : void
    {
        $params = new RouteParams([
            'bookmark' => 'foo',
            'uid' => 'bar',
            'id' => 'baz',
            'type' => 'bat',
            'lang' => 'wiz',
        ]);

        $this->assertSame('foo', $params->getBookmark());
        $this->assertSame('bar', $params->getUid());
        $this->assertSame('baz', $params->getId());
        $this->assertSame('bat', $params->getType());
        $this->assertSame('wiz', $params->getLang());
    }
}
