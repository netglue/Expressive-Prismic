<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

// Infra
use Prophecy\Argument;

// SUT
use ExpressivePrismic\LinkResolver;

// Deps
use Prismic;
use Prismic\Document;
use Prismic\Document\Fragment\LinkInterface;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Document\Fragment\Link\WebLink;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\ExceptionInterface as RouterException;
use ExpressivePrismic\Service\RouteParams;
use ExpressivePrismic\RouteMatcher;
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

    private function resolver()
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
        $link->getUrl()->willReturn('/nuts');
        $this->assertSame('/nuts', $resolver->resolve($link->reveal()));
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
}
