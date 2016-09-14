<?php

namespace ExpressivePrismic\Paginator;

use Prismic\SearchForm;
interface PaginatorFactoryInterface
{

    /**
     * Given a search form, return a paginator that can be iterated over
     * @param SearchForm $form
     * @return mixed
     */
    public function getPaginator(SearchForm $form);

}
