<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

use ExpressivePrismic\RouteMatcher;
use ExpressivePrismic\Service\RouteParams;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteCollector;
use Zend\Expressive\Router\RouterInterface;

class RouteMatcherTest extends TestCase
{

    /** @var RouteCollector */
    private $collector;

    /** @var ObjectProphecy */
    private $router;

    public function setUp() : void
    {
        parent::setUp();
        $this->router = $this->prophesize(RouterInterface::class);
    }

    private function getCollector() : RouteCollector
    {
        if (! $this->collector) {
            /** @var RouterInterface $router */
            $router = $this->router->reveal();
            $this->collector = new RouteCollector($router);
        }
        return $this->collector;
    }

    private function addRoute(string $path, string $name, array $options = []) : Route
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new TextResponse('Hey!');
            }
        };
        $route = $this->getCollector()->route($path, $middleware, ['GET'], $name);
        $route->setOptions($options);
        return $route;
    }

    public function getMatcher() : RouteMatcher
    {
        return new RouteMatcher($this->getCollector(), new RouteParams());
    }

    public function testBookmarkedRouteIsCorrectlyClassifiedAndReturned() : void
    {
        $route = $this->addRoute('/foo', 'test', [
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);
        $matcher = $this->getMatcher();
        $this->assertSame($route, $matcher->getBookmarkedRoute('some-bookmark'));
    }

    public function testTypedRouteIsCorrectlyClassifiedAndReturned() : void
    {
        $route = $this->addRoute('/foo', 'test', [
            'defaults' => [
                'prismic-type' => 'some-type',
            ]
        ]);
        $matcher = $this->getMatcher();
        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
    }

    public function testTypesAsArrayWillReturnTheSameRouteForAllTypes() : void
    {
        $route = $this->addRoute('/foo', 'test', [
            'defaults' => [
                'prismic-type' => ['some-type', 'other-type'],
            ]
        ]);
        $matcher = $this->getMatcher();

        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
        $this->assertSame($route, $matcher->getTypedRoute('other-type'));
    }

    public function testThatTwoRoutesWithTheSameBookmarkWillReturnTheFirstMatchedRoute() : void
    {
        $first = $this->addRoute('/first', 'first', [
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);
        $this->addRoute('/second', 'second', [
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);
        $matcher = $this->getMatcher();
        $this->assertSame($first, $matcher->getBookmarkedRoute('some-bookmark'));
    }

    public function testThatNullIsReturnedWhenNoRouteMatchesBookmark() : void
    {
        $this->addRoute('/first', 'first', [
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);
        $matcher = $this->getMatcher();
        $this->assertNull($matcher->getBookmarkedRoute('other-bookmark'));
    }

    public function testNullIsReturnedWhenNoRouteMatchesType() : void
    {
        $this->addRoute('/foo', 'test', [
            'defaults' => [
                'prismic-type' => ['some-type', 'other-type'],
            ]
        ]);
        $matcher = $this->getMatcher();

        $this->assertNull($matcher->getTypedRoute('wrong-type'));
    }

    public function testThatRoutesWithoutKnownOptionsDoNotMatch() : void
    {
        $this->addRoute('/foo', 'test', []);
        $matcher = $this->getMatcher();
        $this->assertNull($matcher->getTypedRoute('any-type'));
        $this->assertNull($matcher->getBookmarkedRoute('any-bookmark'));
    }
}
