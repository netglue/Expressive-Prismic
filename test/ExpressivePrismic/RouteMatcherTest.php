<?php
declare(strict_types=1);

namespace ExpressivePrismicTest;

// Infra
use ExpressivePrismicTest\TestCase;
use Prophecy\Argument;

// SUT
use ExpressivePrismic\RouteMatcher;
use Zend\Expressive\Router\Route;
use ExpressivePrismic\Service\RouteParams;

class RouteMatcherTest extends TestCase
{

    public function testBookmarkedRouteIsCorrectlyClassifiedAndReturned()
    {
        $route = $this->prophesize(Route::class);
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);
        $route = $route->reveal();

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getBookmarkedRoute('some-bookmark'));
    }

    public function testTypedRouteIsCorrectlyClassifiedAndReturned()
    {
        $route = $this->prophesize(Route::class);
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-type' => 'some-type',
            ]
        ]);
        $route = $route->reveal();

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
    }

    public function testTypesAsArrayWillReturnTheSameRouteForAllTypes()
    {
        $route = $this->prophesize(Route::class);
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-type' => ['some-type', 'other-type'],
            ]
        ]);
        $route = $route->reveal();

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
        $this->assertSame($route, $matcher->getTypedRoute('other-type'));
    }

    /**
     * @expectedException \ExpressivePrismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Route type definitions for Prismic routes must be a string or an array
     */
    public function testExceptionThrownWhenTypeIsNotAStringOrAnArray()
    {
        $route = $this->prophesize(Route::class);
        $route->getOptions()->willReturn([
            'defaults' => [
                'prismic-type' => 1,
            ]
        ]);

        $matcher = new RouteMatcher([$route->reveal()], new RouteParams);
    }
}
