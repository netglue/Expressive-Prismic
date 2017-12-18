<?php
declare(strict_types=1);

namespace ExpressivePrismic\View\Helper;
use ExpressivePrismicTest\TestCase;

use ExpressivePrismic\View\Helper\Url;
use Prismic;

class UrlTest extends TestCase
{

    private $resolver;

    private $api;

    public function setUp()
    {
        $this->resolver = $this->prophesize(Prismic\LinkResolver::class);
        $this->api = $this->prophesize(Prismic\Api::class);
    }

    public function getHelper()
    {
        return new Url(
            $this->api->reveal(),
            $this->resolver->reveal()
        );
    }

    public function testStringIdIsResolved()
    {
        $id = 'testId';
        $doc = $this->prophesize(Prismic\Document::class)->reveal();

        $this->resolver->resolveDocument($doc)->willReturn('/some-url');
        $this->api->getByID($id)->willReturn($doc);

        $helper = $this->getHelper();

        $this->assertSame('/some-url', $helper($id));
    }

    public function testUnknownIdReturnsNull()
    {
        $id = 'testId';

        $this->resolver->resolveDocument()->shouldNotBeCalled();
        $this->resolver->resolve()->shouldNotBeCalled();

        $this->api->getByID($id)->willReturn(null);
        $helper = $this->getHelper();
        $this->assertNull($helper($id));
    }

    public function testDocumentIsResolved()
    {
        $doc = $this->prophesize(Prismic\Document::class)->reveal();
        $this->resolver->resolveDocument($doc)->willReturn('/some-url');
        $this->api->getByID()->shouldNotBeCalled();
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($doc));
    }

    public function testLinkIsResolved()
    {
        $link = $this->prophesize(Prismic\Fragment\Link\LinkInterface::class)->reveal();
        $this->resolver->resolve($link)->willReturn('/some-url');
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($link));
    }

}
