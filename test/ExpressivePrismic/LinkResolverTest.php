<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use ExpressivePrismic\LinkResolver;
use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Document\Fragment\Link\WebLink;
use Prophecy\Argument;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Route;

class LinkResolverTest extends TestCase
{

    private $url;

    private $matcher;

    public function setUp() : void
    {
        $this->url = $this->prophesize(UrlHelper::class);
        $this->matcher = $this->prophesize(RouteMatcher::class);
    }

    private function resolver() : LinkResolver
    {
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

    public function testBrokenLinkReturnsNull() : void
    {
        $resolver = $this->resolver();
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(true);

        $this->assertNull($resolver->resolve($link->reveal()));
    }

    public function testLinkWithoutMatchingBookmarkRouteOrTypeRouteWillReturnNull() : void
    {
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('no-match');
        $link->getType()->willReturn('typename');

        $this->matcher->getTypedRoute('typename')->willReturn(null);

        $resolver = $this->resolver();
        $this->assertNull($resolver->resolve($link->reveal()));
    }

    public function testNonDocumentLinkWillReturnUrl() : void
    {
        $resolver = $this->resolver();
        $link = $this->prophesize(WebLink::class);
        $link->getUrl()->willReturn('/nuts');
        $this->assertSame('/nuts', $resolver->resolve($link->reveal()));
    }

    public function testBookmarkedLinkWithoutMatchingRouteIsTriedAsType() : void
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

    public function testLinkWithMatchingTypedRouteIsResolved() : void
    {
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('not-match-bookmark');
        $link->getType()->willReturn('mytype');

        $link->getUid()->shouldBeCalled();
        $link->getLang()->shouldBeCalled();

        $route = $this->prophesize(Route::class);
        $route->getName()->willReturn('RouteName');
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-type' => 'mytype'
            ]
        ]);
        $this->matcher->getTypedRoute('mytype')->willReturn($route->reveal());
        $this->url->generate('RouteName', Argument::type('array'))->willReturn('/foo');

        $resolver = $this->resolver();
        $this->assertSame('/foo', $resolver->resolve($link->reveal()));
    }

    public function testDocumentLinkWillBeResolved() : void
    {
        /** @var DocumentLink $link */
        $link = $this->prophesize(DocumentLink::class);
        $link->isBroken()->willReturn(false);
        $link->getId()->willReturn('document-id');
        $link->getUid()->willReturn(null);
        $link->getType()->willReturn('foo');
        $link->getTags()->willReturn([]);
        $link->getSlug()->willReturn('foo');
        $link->getLang()->willReturn(null);

        $route = $this->prophesize(Route::class);
        $route->getName()->willReturn('RouteName');
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-bookmark' => 'bookmark-name'
            ]
        ]);


        $this->matcher->getBookmarkedRoute('bookmark-name')->willReturn($route);
        $this->url->generate('RouteName', Argument::type('array'))->willReturn('/foo');

        $resolver = $this->resolver();
        $this->assertSame('/foo', $resolver->resolve($link->reveal()));
    }
}
