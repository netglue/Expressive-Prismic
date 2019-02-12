<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Container;

use ExpressivePrismic\Container\ApiClientFactory;
use ExpressivePrismic\Exception;
use ExpressivePrismicTest\TestCase;
use Psr\Container\ContainerInterface;

class ApiClientFactoryTest extends TestCase
{
    private $container;

    public function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testExceptionIsThrownWhenConfigIsNotAvailable() : void
    {
        $this->container->has('config')->willReturn(false);
        $factory = new ApiClientFactory;
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No configuration can be found');
        $factory($this->container->reveal());
    }

    public function testExceptionIsThrownWhenPrismicConfigIsNotAvailable() : void
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $factory = new ApiClientFactory;
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No Prismic API configuration can be found');
        $factory($this->container->reveal());
    }

    public function testExceptionIsThrownWhenApiUrlHasNotBeenSet() : void
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'prismic' => [
                'api' => [

                ],
            ]
        ]);
        $factory = new ApiClientFactory;
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Prismic API endpoint URL must be specified');
        $factory($this->container->reveal());
    }

    public function testContainerWillBeAskedForACacheInstanceIfCacheParameterIsNonEmpty() : void
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'prismic' => [
                'api' => [
                    'url' => 'foo',
                    'cache' => 'SomeCache',
                ],
            ]
        ]);
        $this->container->get('SomeCache')->shouldBeCalled();
        $factory = new ApiClientFactory;
        try {
            $factory($this->container->reveal());
        } catch (Exception\RuntimeException $e) {
        }
    }
}
