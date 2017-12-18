<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\LinkResolver;

// Deps
use Prismic;
use Prismic\Fragment\Link\LinkInterface;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\WebLink;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\Exception\ExceptionInterface as RouterException;
use ExpressivePrismic\Service\RouteParams;
use Zend\Expressive\Application;



class LinkResolverTest extends TestCase
{

    private $url;
    private $api;
    private $app;

    public function setUp()
    {
        $this->url = $this->prophesize(UrlHelper::class);
        $this->api = $this->prophesize(Prismic\Api::class);
        $this->app = $this->prophesize(Application::class);
    }

    public function getResolver()
    {
        return new LinkResolver(
            $this->api->reveal(),
            new RouteParams,
            $this->url->reveal(),
            $this->app->reveal()
        );
    }

    public function testNonLinkReturnsNull()
    {
        $this->assertNull($this->getResolver()->resolve('foo'));
    }

    public function testWebLinkReturnsUrl()
    {
        $link = new WebLink('http://example.com');
        $this->assertSame('http://example.com', $this->getResolver()->resolve($link));
    }

    public function testBrokenLinkReturnsNull()
    {
        $link = $this->prophesize(DocumentLink::class());
        $link->isBroken()->willReturn(true);
        $this->assertNull($this->getResolver()->resolve($link));
    }

    public function testBookmarkedLinkIsResolved()
    {
        $documentId = 'bookmarkedDocumentId';

        $link = new Link\DocumentLink($documentId, 'docUid', 'docType', [], 'foo', 'lang', [], false);

        $url = $this->resolver->resolve($link);
        $this->assertSame('/bookmarked-route', $url);
    }

    public function testUnroutableLinkResolvesToNull()
    {
        $link = new Link\DocumentLink('notBookmarkedId', 'docUid', 'docType', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertNull($url);
    }

    public function testGenericIdOnlyRouteResolves()
    {
        // Add route here so that we can test for not matching any route previously
        $this->app->route('/match-id/{prismic-id}', new TestMiddleware, ['GET'], 'matchGenericId')
            ->setOptions([
                'defaults' => [
                    'prismic-id' => null,
                ],
            ]);

        $link = new Link\DocumentLink('notBookmarkedId', 'docUid', 'docType', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/match-id/notBookmarkedId', $url);

        $link = new Link\DocumentLink('unroutedId', 'docUid', 'docType', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/match-id/unroutedId', $url);
    }

    public function testTypedLinkWithIdIsResolved()
    {
        $link = new Link\DocumentLink('foo', 'MyUid', 'MyType', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/match-type-with-id/foo', $url);
    }

    public function testTypedLinkWithUidIsResolved()
    {
        $link = new Link\DocumentLink('foo', 'MyUid', 'MyOtherType', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/match-type-with-uid/MyUid', $url);
    }

    public function testTypedLinksAreMatchedInArray()
    {
        $link = new Link\DocumentLink('foo', 'MyUid', 'Type1', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/Type1/MyUid', $url);

        $link = new Link\DocumentLink('foo', 'MyUid', 'Type2', [], 'foo', 'lang', [], false);
        $url = $this->resolver->resolve($link);
        $this->assertSame('/Type2/MyUid', $url);
    }



}

class TestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new TextResponse('Response');
    }
}
