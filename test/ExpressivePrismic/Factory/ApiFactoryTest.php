<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Factory;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Factory\ApiFactory;

// Deps
use Psr\Container\ContainerInterface;
use Prismic\Api;
use ExpressivePrismic\Exception;

class ApiFactoryTest extends TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No configuration can be found
     */
    public function testExceptionIsThrownWhenConfigIsNotAvailable()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new ApiFactory;
        $factory($this->container->reveal());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage No Prismic API configuration can be found
     */
    public function testExceptionIsThrownWhenPrismicConfigIsNotAvailable()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $factory = new ApiFactory;
        $factory($this->container->reveal());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Prismic API endpoint URL must be specified
     */
    public function testExceptionIsThrownWhenApiUrlHasNotBeenSet()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'prismic' => [
                'api' => [

                ],
            ]
        ]);
        $factory = new ApiFactory;
        $factory($this->container->reveal());
    }

    /**
     * @expectedException ExpressivePrismic\Exception\RuntimeException
     * @expectedExceptionMessage Exception thrown creating API instance.
     */
    public function testExceptionThrownForInvalidUrl()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'prismic' => [
                'api' => [
                    'url' => 'foo',
                ],
            ]
        ]);
        $factory = new ApiFactory;
        $factory($this->container->reveal());
    }

    public function testContainerWillBeAskedForACacheInstanceIfCacheParameterIsNonEmpty()
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
        $factory = new ApiFactory;
        try {
            $factory($this->container->reveal());
        } catch (Exception\RuntimeException $e) {
        }
    }
}
