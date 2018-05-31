<?php
declare(strict_types=1);

namespace ExpressivePrismic\Paginator\Adapter;

use Prismic\SearchForm;
use Prismic\Response;
use Zend\Paginator\Adapter\AdapterInterface;

class ZendPaginatorAdapter implements AdapterInterface
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
        if ($offset > 0) {
            $page = (int) floor($offset / $itemCountPerPage) + 1;
        }

        /** @var SearchForm **/
        $form = $this->form->pageSize($itemCountPerPage);
        /** @var SearchForm **/
        $form = $form->page($page);
        /** @var Response **/
        $response = $form->submit();

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
