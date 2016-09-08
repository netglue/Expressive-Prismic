<?php

namespace ExpressivePrismic;

use Prismic;
use Prismic\Fragment\Link;
use Zend\Expressive\Helper\UrlHelper;

class LinkResolverTest extends \PHPUnit_Framework_TestCase
{

    private $api;
    private $routeParams;
    private $router;
    private $urlHelper;
    private $routesConfig;

    public function setUp()
    {
        $this->api = $this->createMock(Prismic\Api::class);
        $this->routeParams = new Service\RouteParams([
            'id' => 'id',
            'bookmark' => 'bookmark',
            'uid' => 'uid',
            'type' => 'type',
        ]);
        $this->routesConfig = [
            'matchingBookmark' => [
                'name' => 'matchingBookmark',
                'options' => [
                    'defaults' => [
                        'bookmark' => 'bookmarkName',
                    ],
                ],
            ],
            'bookmarkUnknown' => [
                'name' => 'bookmarkUnknown',
                'options' => [
                    'defaults' => [
                        'bookmark' => 'bookmarkNotKnownInTheApi',
                    ],
                ],
            ],
            'matchingTypeUid' => [
                'name' => 'matchingTypeUid',
                'options' => [
                    'defaults' => [
                        'uid' => null,
                        'type' => 'MyType',
                    ],
                ],
            ],
            'nonMatchingTypeId' => [
                'name' => 'nonMatchingTypeId',
                'options' => [
                    'defaults' => [
                        'id' => null,
                        'type' => 'MyType',
                    ],
                ],
            ],
            'matchingTypeId' => [
                'name' => 'matchingTypeId',
                'options' => [
                    'defaults' => [
                        'id' => null,
                        'type' => 'MyOtherType',
                    ],
                ],
            ],
            'matchingId' => [
                'name' => 'matchingId',
                'options' => [
                    'defaults' => [
                        'id' => '',
                    ],
                ],
            ],
            'someStaticRoute' => [
                'name' => 'someStaticRoute',
            ],
        ];

        $this->router = new MockRouter(array_fill_keys(array_keys($this->routesConfig), '/expectedUrl'));
        $this->urlHelper = new UrlHelper($this->router);
        $this->resolver = new LinkResolver($this->api, $this->routeParams, $this->routesConfig, $this->urlHelper);

        $this->api->method('bookmarks')
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
        $link = new Link\DocumentLink('foo', 'foo', 'foo', ['foo'], 'foo', [], true);
        $this->assertNull($this->resolver->resolve($link));
    }

    public function testBookmarkedLinkIsResolved()
    {
        $documentId = 'bookmarkedDocumentId';

        $link = new Link\DocumentLink($documentId, 'docUid', 'docType', [], 'foo', [], false);

        $url = $this->resolver->resolve($link);
        $data = json_decode($url, true);

        $this->assertSame($documentId, $data['params']['id']);
        $this->assertSame('docUid', $data['params']['uid']);
        $this->assertSame('docType', $data['params']['type']);

        $link = new Link\DocumentLink('notBookmarkedId', 'docUid', 'docType', [], 'foo', [], false);
        $this->assertMatchedGenericIdRoute($this->resolver->resolve($link));

        $link = new Link\DocumentLink('unroutedId', 'docUid', 'docType', [], 'foo', [], false);
        $this->assertMatchedGenericIdRoute($this->resolver->resolve($link));
    }

    public function assertMatchedGenericIdRoute($url)
    {
        $data = json_decode($url, true);
        $this->assertSame('matchingId', $data['routeName']);
    }

    public function testTypedLinkIsResolved()
    {
        $link = new Link\DocumentLink('foo', 'MyUid', 'MyType', [], 'foo', [], false);
        $url = $this->resolver->resolve($link);
        $data = json_decode($url, true);
        $this->assertSame('matchingTypeUid', $data['routeName']);

        $link = new Link\DocumentLink('foo', 'MyUid', 'MyOtherType', [], 'foo', [], false);
        $url = $this->resolver->resolve($link);
        $data = json_decode($url, true);
        $this->assertSame('matchingTypeId', $data['routeName']);
    }

}
