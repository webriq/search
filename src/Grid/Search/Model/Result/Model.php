<?php

namespace Grid\Search\Model\Result;

use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;

/**
 * Model
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Model implements MapperAwareInterface
{

    use MapperAwareTrait;

    /**
     * Constructor
     *
     * @param   \Grid\Search\Model\Result\Mapper    $searchResultMapper
     */
    public function __construct( Mapper $searchResultMapper )
    {
        $this->setMapper( $searchResultMapper );
    }

    /**
     * Get search count
     *
     * @param   mixed   $where
     * @return  int
     */
    public function searchCount( $where )
    {
        return $this->getMapper()
                    ->findCount( $where );
    }

    /**
     * Get search results
     *
     * @param   mixed   $where
     * @return  Structure[]
     */
    public function searchResults( $where, $limit = 10, $offset = 0 )
    {
        return $this->getMapper()
                    ->findAll(
                        $where,
                        null,
                        (int) $limit  ?: 10,
                        (int) $offset ?: 0
                    );
    }

    /**
     * Get search suggestions
     *
     * @param   mixed   $where
     * @param   int     $limit
     * @return  \Traversable
     */
    public function searchSuggestions( $where, $limit = 10 )
    {
        return $this->getMapper()
                    ->findSuggestions(
                        $where,
                        (int) $limit ?: 10
                    );
    }

    /**
     * Debug search query
     *
     * @param   string  $locale
     * @param   string  $query
     * @return  string
     */
    public function debugQuery( $locale, $query )
    {
        return $this->getMapper()
                    ->debugQuery( $locale, $query );
    }

    /**
     * Get paginator
     *
     * @param   mixed   $where
     * @return  \Zend\Paginator\Paginator
     */
    public function getPaginator( $where )
    {
        return $this->getMapper()
                    ->getPaginator( $where );
    }

}
