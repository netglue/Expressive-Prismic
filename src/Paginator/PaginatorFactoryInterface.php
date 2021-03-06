<?php
declare(strict_types=1);

namespace ExpressivePrismic\Paginator;

use Prismic\SearchForm;

/**
 * Interface PaginatorFactoryInterface
 *
 * @package ExpressivePrismic\Paginator
 */
interface PaginatorFactoryInterface
{

    /**
     * Given a search form, return a paginator that can be iterated over
     * @param SearchForm $form
     * @return mixed
     */
    public function getPaginator(SearchForm $form);
}
