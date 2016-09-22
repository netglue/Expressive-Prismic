<?php

namespace ExpressivePrismic\Paginator;

use Prismic\SearchForm;
use Zend\Paginator\Paginator;

/**
 * Class ZendPaginatorFactory
 *
 * @package ExpressivePrismic\Paginator
 */
class ZendPaginatorFactory implements PaginatorFactoryInterface
{

    /**
     * Given a search form, return a paginator that can be iterated over
     * @param SearchForm $form
     * @return Paginator
     */
    public function getPaginator(SearchForm $form) : Paginator
    {
        $adapter = new Adapter\ZendPaginatorAdapter($form);

        return new Paginator($adapter);
    }

}
