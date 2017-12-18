<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Paginator\Adapter;

// Infra
use ExpressivePrismicTest\TestCase;

// SUT
use ExpressivePrismic\Paginator\Adapter\ZendPaginatorAdapter;

// Deps
use Prismic\SearchForm;
use Prismic\Response;

class ZendPaginatorAdapterTest extends TestCase
{

    public function testCount()
    {
        $form = $this->prophesize(SearchForm::class);
        $form->count()->willReturn(10);

        $adapter = new ZendPaginatorAdapter($form->reveal());

        $this->assertSame(10, $adapter->count());
    }

    public function testItemCountAndPageNumberAreGivenToForm()
    {
        $response = $this->prophesize(Response::class);
        $response->getResults()->shouldBeCalled();

        $form = $this->prophesize(SearchForm::class);
        $form->count()->willReturn(100);
        $form->submit()->willReturn($response->reveal());

        $mock = null;

        $form->page(2)->willReturn($form->reveal());
        $form->pageSize(10)->willReturn($form->reveal());

        $mock = $form->reveal();

        $adapter = new ZendPaginatorAdapter($form->reveal());

        $itemOffset = 11;
        $perPage = 10;
        $adapter->getItems($itemOffset, $perPage);

    }

}
