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
        $route = new Route('/foo', function(){});
        $route->setOptions([
            'defaults' => [
                'prismic-bookmark' => 'some-bookmark',
            ]
        ]);

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getBookmarkedRoute('some-bookmark'));
    }

    public function testTypedRouteIsCorrectlyClassifiedAndReturned()
    {
        $route = new Route('/foo', function(){});
        $route->setOptions([
            'defaults' => [
                'prismic-type' => 'some-type',
            ]
        ]);

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
    }

    public function testTypesAsArrayWillReturnTheSameouteForAllTypes()
    {
        $route = new Route('/foo', function(){});
        $route->setOptions([
            'defaults' => [
                'prismic-type' => ['some-type', 'other-type'],
            ]
        ]);

        $matcher = new RouteMatcher([$route], new RouteParams);

        $this->assertSame($route, $matcher->getTypedRoute('some-type'));
        $this->assertSame($route, $matcher->getTypedRoute('other-type'));
    }

    /**
     * @expectedException ExpressivePrismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Route type definitions for Prismic routes must be a string or an array
     */
    public function testExceptionThrownWhenTypeIsNotAStringOrAnArray()
    {
        $route = new Route('/foo', function(){});
        $route->setOptions([
            'defaults' => [
                'prismic-type' => 1,
            ]
        ]);

        $matcher = new RouteMatcher([$route], new RouteParams);
    }


}
