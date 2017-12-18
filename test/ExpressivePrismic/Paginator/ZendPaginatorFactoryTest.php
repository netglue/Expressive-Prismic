<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Paginator;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Paginator\ZendPaginatorFactory;

// Deps
use Prismic\SearchForm;
use ExpressivePrismic\Paginator\Adapter\ZendPaginatorAdapter;
use Zend\Paginator\Paginator;

class ZendPaginatorFactoryTest extends TestCase
{

    public function testPagerWithCorrectAdapterIsConstructed()
    {
        $form = $this->prophesize(SearchForm::class);
        $form->count()->shouldBeCalled();

        $factory = new ZendPaginatorFactory;
        $pager = $factory->getPaginator($form->reveal());

        $this->assertInstanceOf(Paginator::class, $pager);
        $this->assertInstanceOf(ZendPaginatorAdapter::class, $pager->getAdapter());
    }

}
