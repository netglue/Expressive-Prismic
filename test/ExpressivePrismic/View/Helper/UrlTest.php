<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper;

use ExpressivePrismic\View\Helper\Url;
use ExpressivePrismicTest\TestCase;
use Prismic;
use Prismic\Document\Fragment\Link\DocumentLink;

class UrlTest extends TestCase
{

    /**
     * @var Prismic\LinkResolver
     */
    private $resolver;

    /**
     * @var Prismic\Api
     */
    private $api;

    public function setUp() : void
    {
        $this->resolver = $this->prophesize(Prismic\LinkResolver::class);
        $this->api = $this->prophesize(Prismic\Api::class);
    }

    private function getHelper() : Url
    {
        return new Url(
            $this->api->reveal()
        );
    }

    public function testStringIdIsResolved() : void
    {
        $id = 'testId';
        $doc = $this->prophesize(Prismic\DocumentInterface::class);
        $link = $this->prophesize(DocumentLink::class);
        $link->getUrl()->willReturn('/some-url');
        $doc->asLink()->willReturn($link->reveal());
        $this->api->getById($id)->willReturn($doc->reveal());


        $this->resolver->resolve($link)->willReturn('/some-url');

        $helper = $this->getHelper();

        $this->assertSame('/some-url', $helper($id));
    }

    public function testUnknownIdReturnsNull() : void
    {
        $id = 'testId';

        $this->resolver->resolve()->shouldNotBeCalled();

        $this->api->getById($id)->willReturn(null);
        $helper = $this->getHelper();
        $this->assertNull($helper($id));
    }

    public function testDocumentIsResolved() : void
    {
        $doc = $this->prophesize(Prismic\DocumentInterface::class);
        $link = $this->prophesize(DocumentLink::class);
        $link->getUrl()->willReturn('/some-url');
        $doc->asLink()->willReturn($link->reveal());
        $this->api->getById()->shouldNotBeCalled();
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($doc->reveal()));
    }

    public function testLinkIsResolved() : void
    {
        $link = $this->prophesize(Prismic\Document\Fragment\LinkInterface::class);
        $link->getUrl()->willReturn('/some-url');
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($link->reveal()));
    }
}
