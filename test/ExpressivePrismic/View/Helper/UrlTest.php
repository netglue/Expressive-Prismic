<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\View\Helper;

// Infra
use ExpressivePrismic\LinkResolver;
use ExpressivePrismicTest\TestCase;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\View\Helper\Url;

// Deps
use Prismic;

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
        $doc = $this->prophesize(Prismic\DocumentInterface::class);
        $link = $this->prophesize(DocumentLink::class);
        $link->getUrl()->willReturn('/some-url');
        $doc->asLink()->willReturn($link->reveal());
        $this->api->getById($id)->willReturn($doc->reveal());


        $this->resolver->resolve($link)->willReturn('/some-url');

        $helper = $this->getHelper();

        $this->assertSame('/some-url', $helper($id));
    }

    public function testUnknownIdReturnsNull()
    {
        $id = 'testId';

        $this->resolver->resolve()->shouldNotBeCalled();

        $this->api->getById($id)->willReturn(null);
        $helper = $this->getHelper();
        $this->assertNull($helper($id));
    }

    public function testDocumentIsResolved()
    {
        $doc = $this->prophesize(Prismic\DocumentInterface::class);
        $link = $this->prophesize(DocumentLink::class);
        $link->getUrl()->willReturn('/some-url');
        $doc->asLink()->willReturn($link->reveal());
        $this->api->getById()->shouldNotBeCalled();
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($doc->reveal()));
    }

    public function testLinkIsResolved()
    {
        $link = $this->prophesize(Prismic\Document\Fragment\LinkInterface::class);
        $link->getUrl()->willReturn('/some-url');
        $helper = $this->getHelper();
        $this->assertSame('/some-url', $helper($link->reveal()));
    }
}
