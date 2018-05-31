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
        $this->assertSame('prismic-uid', $params->getUid());
        $this->assertSame('prismic-id', $params->getId());
        $this->assertSame('prismic-type', $params->getType());
        $this->assertSame('prismic-lang', $params->getLang());
    }

    public function testConstructWithValuesReturnsExpected()
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
