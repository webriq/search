<?php

namespace Grid\Search\Query;

/**
 * AbstractData
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
abstract class AbstractData implements ExpressionInterface
{

    /**
     * @var string
     */
    protected $data;

    /**
     * Constructor
     *
     * @param   string  $data
     */
    public function __construct( $data = null )
    {
        if ( $data )
        {
            $this->setData( $data );
        }
    }

    /**
     * Set data
     *
     * @param   string  $data
     * @return  AbstractData
     */
    public function setData( $data )
    {
        $this->data = (string) $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return  string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return  bool
     */
    public function isEmpty()
    {
        return empty( $this->data );
    }

    /**
     * @return  string
     */
    public function toQueryString()
    {
        return $this->data;
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        return $this->data;
    }

}
