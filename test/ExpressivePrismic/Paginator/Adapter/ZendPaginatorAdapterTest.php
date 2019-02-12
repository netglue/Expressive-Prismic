<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Paginator\Adapter;

use ExpressivePrismic\Paginator\Adapter\ZendPaginatorAdapter;
use ExpressivePrismicTest\TestCase;
use Prismic\Response;
use Prismic\SearchForm;
use Prophecy\Prophecy\ObjectProphecy;

class ZendPaginatorAdapterTest extends TestCase
{

    public function testCount() : void
    {
        $form = $this->prophesize(SearchForm::class);
        $form->count()->willReturn(10);

        $adapter = new ZendPaginatorAdapter($form->reveal());

        $this->assertSame(10, $adapter->count());
    }

    public function testItemCountAndPageNumberAreGivenToForm() : void
    {
        $response = $this->prophesize(Response::class);
        $response->getResults()->shouldBeCalled();

        /** @var ObjectProphecy|SearchForm $form **/
        $form = $this->prophesize(SearchForm::class);
        $form->count()->willReturn(100);
        $form->submit()->willReturn($response->reveal());

        $mock = null;

        $form->page(2)->willReturn($form->reveal());
        $form->pageSize(10)->willReturn($form->reveal());

        /** @var ObjectProphecy|SearchForm  $mock **/
        $mock = $form->reveal();

        $adapter = new ZendPaginatorAdapter($form->reveal());

        $itemOffset = 11;
        $perPage = 10;
        $adapter->getItems($itemOffset, $perPage);
    }
}
