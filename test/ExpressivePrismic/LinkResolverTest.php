<?php

namespace ExpressivePrismic;

use Prismic;
use Prismic\Fragment\Link;
use Zend\Expressive\Helper\UrlHelper;
use Bootstrap;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\Route;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class LinkResolverTest extends \PHPUnit_Framework_TestCase
{

    private $app;


    public static function setUpBeforeClass()
    {
        $bootstrap         = Bootstrap::getInstance();
        $app = $bootstrap->app;
        $middleware = new TestMiddleware;
        $app->route('/bookmarked-route', $middleware, ['GET'], 'matchingBookmark')
            ->setOptions([
                'defaults' => [
                    'prismic-bookmark' => 'bookmarkName',
                ],
            ]);



        $app->route('/match-type-with-id/{prismic-id}', $middleware, ['GET'], 'matchTypeAndId')
            ->setOptions([
                'defaults' => [
                    'prismic-type' => 'MyType',
                ],
            ]);

        $app->route('/match-type-with-uid/{prismic-uid}', $middleware, ['GET'], 'matchTypeAndUid')
            ->setOptions([
                'defaults' => [
                    'prismic-type' => 'MyOtherType',
                ],
            ]);
    }

    public function setUp()
    {
        $bootstrap         = Bootstrap::getInstance();
        $urlHelper         = $bootstrap->container->get(UrlHelper::class);
        $api               = $this->createMock(Prismic\Api::class);
        $routeParams       = $bootstrap->container->get(Service\RouteParams::class);
        $app = $this->app  = $bootstrap->app;

        $this->resolver    = new LinkResolver($api, $routeParams, $urlHelper, $app);

        $api->method('bookmarks')
            ->willReturn([
              'bookmarkName' => 'bookmarkedDocumentId',
              'unroutedBookmark' => 'unroutedId',
            ]);
    }

    public function testNonLinkReturnsNull()
    {
        $this->assertNull($this->resolver->resolve('foo'));
    }

    public function testWebLinkReturnsUrl()
    {
        $link = new Link\WebLink('http://example.com');
        $this->assertSame('http://example.com', $this->resolver->resolve($link));
    }

    public function testBrokenLinkReturnsNull()
    {
        $link = new Link\DocumentLink('foo', 'foo', 'foo', ['foo'], 'foo', 'en', [], true);
        $this->assertNull($this->resolver->resolve($link));
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



}

class TestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new TextResponse('Response');
    }
}
