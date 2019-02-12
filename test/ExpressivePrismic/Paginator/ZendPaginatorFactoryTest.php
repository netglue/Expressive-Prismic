<?php
declare(strict_types=1);

namespace ExpressivePrismicTest\Paginator;

use ExpressivePrismic\Paginator\Adapter\ZendPaginatorAdapter;
use ExpressivePrismic\Paginator\ZendPaginatorFactory;
use ExpressivePrismicTest\TestCase;
use Prismic\SearchForm;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Paginator\Paginator;

class ZendPaginatorFactoryTest extends TestCase
{

    public function testPagerWithCorrectAdapterIsConstructed() : void
    {
        /** @var ObjectProphecy|SearchForm  $form **/
        $form = $this->prophesize(SearchForm::class);
        $form->count()->shouldBeCalled();

        $factory = new ZendPaginatorFactory;
        $pager = $factory->getPaginator($form->reveal());

        $this->assertInstanceOf(Paginator::class, $pager);
        $this->assertInstanceOf(ZendPaginatorAdapter::class, $pager->getAdapter());
    }
}
