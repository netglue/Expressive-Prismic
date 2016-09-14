<?php

namespace ExpressivePrismic\Paginator;

use Prismic\SearchForm;
use Zend\Paginator\Paginator;

class ZendPaginatorFactory implements PaginatorFactoryInterface
{

    /**
     * Given a search form, return a paginator that can be iterated over
     * @param SearchForm $form
     * @return mixed
     */
    public function getPaginator(SearchForm $form)
    {
        $adapter = new Adapter\ZendPaginatorAdapter($form);
        return new Paginator($adapter);
    }

}
