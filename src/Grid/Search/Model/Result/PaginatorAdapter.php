<?php

namespace Grid\Search\Model\Result;

use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * PaginatorAdapter
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class PaginatorAdapter implements AdapterInterface, MapperAwareInterface
{

    use MapperAwareTrait;

    /**
     * @var mixed|null
     */
    protected $where;

    /**
     * @var mixed|null
     */
    protected $options;

    /**
     * @var mixed|null
     */
    private $count;

    /**
     * Constructor
     *
     * @param   Mapper      $searchResultMapper
     * @param   mixed|null  $where
     * @param   mixed|null  $options
     */
    public function __construct( Mapper $searchResultMapper, $where = null, $options = null )
    {
        $this->setMapper( $searchResultMapper );
        $this->where    = $where;
        $this->options  = $options;
    }

    /**
     * Count elements of an object
     *
     * @return  int
     */
    public function count()
    {
        if ( null === $this->count )
        {
            $this->count = $this->getMapper()
                                ->findCount( $this->where );
        }

        return $this->count;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param   int     $offset             Page offset
     * @param   int     $itemCountPerPage   Number of items per page
     * @return  array
     */
    public function getItems( $offset, $itemCountPerPage )
    {
        return $this->getMapper()
                    ->findAll( $this->where,
                               $this->options,
                               $itemCountPerPage,
                               $offset );
    }

}
