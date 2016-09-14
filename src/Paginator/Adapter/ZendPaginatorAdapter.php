<?php

namespace ExpressivePrismic\Paginator\Adapter;

use Prismic\SearchForm;
use Zend\Paginator\Adapter\AdapterInterface;

class ZendPaginatorAdapter implements AdapterInterface,
{
    /**
     * ArrayAdapter
     *
     * @var SearchForm
     */
    protected $form = null;

    /**
     * Item count
     *
     * @var int
     */
    protected $count = null;

    /**
     * Constructor.
     *
     * @param SearchForm $form SearchForm to paginate
     */
    public function __construct(SearchForm $form)
    {
        $this->form = $form;
        $this->count = $this->form->count();
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $page = 1;
        if($offset > 0) {
            $page = ($offset / $itemCountPerPage) + 1;
        }

        $response = $this->form->pageSize($itemCountPerPage)->page($page)->submit();

        return $response->getResults();
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }
}
