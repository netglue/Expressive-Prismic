<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use Prismic\Document;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\WebLink;
use Prophecy\Argument;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Route;

class LinkResolverTest extends TestCase
{

    private $url;

    private $matcher;

    public function setUp()
    {
        $this->url = $this->prophesize(UrlHelper::class);
        $this->matcher = $this->prophesize(RouteMatcher::class);
    }

    private function resolver() {
        $bookmarks = [
            'bookmark-name' => 'document-id',
        ];
        return new LinkResolver(
            $bookmarks,
            new RouteParams,
            $this->url->reveal(),
            $this->matcher->reveal()
        );
    }

    public function testResolveReturnsNullForANonLink()
    {
        $resolver = $this->resolver();
        $this->assertNull($resolver->resolve('foo'));
    }

    public function testBrokenLinkReturnsNull()
    {
        $resolver = $this->resolver();
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(true);

        $this->assertNull($resolver->resolve($link->reveal()));
    }

    public function testLinkWithoutMatchingBookmarkRouteOrTypeRouteWillReturnNull()
    {
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('no-match');
        $link->getType()->willReturn('typename');

        $this->matcher->getTypedRoute('typename')->willReturn(null);

        $resolver = $this->resolver();
        $this->assertNull($resolver->resolve($link->reveal()));
    }

    public function testNonDocumentLinkWillReturnUrl()
    {
        $resolver = $this->resolver();
        $link = $this->prophesize(WebLink::class);
        $link->getUrl($resolver)->willReturn('/nuts');
        $this->assertSame('/nuts', $resolver->resolve($link->reveal()));
    }

    public function testDocumentWillBeResolvedAsLink()
    {
        $doc = $this->prophesize(Document::class);
        $doc->getId()->willReturn('document-id');
        $doc->getUid()->willReturn(null);
        $doc->getType()->willReturn('foo');
        $doc->getTags()->willReturn([]);
        $doc->getSlug()->willReturn('foo');
        $doc->getLang()->willReturn(null);
        $doc->getFragments()->willReturn([]);

        $route = new Route('/foo', function(){});
        $route->setName('RouteName');
        $route->setOptions([
            'defaults' => [
                'prismic-bookmark' => 'bookmark-name'
            ]
        ]);


        $this->matcher->getBookmarkedRoute('bookmark-name')->willReturn($route);
        $this->url->generate(
            'RouteName',
            Argument::type('array'),
            [],
            null,
            ['reuse_result_params' => false]
        )->willReturn('/foo');

        $resolver = $this->resolver();
        $this->assertSame('/foo', $resolver->resolve($doc->reveal()));
    }

    public function testBookmarkedLinkWithoutMatchingRouteIsTriedAsType()
    {
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('document-id');
        $link->getType()->willReturn('none');
        $this->matcher->getBookmarkedRoute('bookmark-name')->willReturn(null); // No Matching Route for bookmark name
        $this->matcher->getTypedRoute('none')->willReturn(null); // No Matching type route

        $resolver = $this->resolver();
        $this->assertNull($resolver->resolve($link->reveal()));
    }

    public function testLinkWithMatchingTypedRouteIsResolved()
    {
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('not-match-bookmark');
        $link->getType()->willReturn('mytype');

        $link->getUid()->shouldBeCalled();
        $link->getLang()->shouldBeCalled();

        $route = new Route('/foo', function(){});
        $route->setName('RouteName');
        $route->setOptions([
            'defaults' => [
                'prismic-type' => 'mytype'
            ]
        ]);
        $this->matcher->getTypedRoute('mytype')->willReturn($route);
        $this->url->generate(
            'RouteName',
            Argument::type('array'),
            [],
            null,
            ['reuse_result_params' => false]
        )->willReturn('/foo');

        $resolver = $this->resolver();
        $this->assertSame('/foo', $resolver->resolve($link->reveal()));
    }
}
