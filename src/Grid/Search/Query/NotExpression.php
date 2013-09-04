<?php

namespace Grid\Search\Query;

/**
 * NotExpression
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class NotExpression implements ExpressionInterface
{

    /**
     * @var ExpressionInterface
     */
    protected $innerException;

    /**
     * Constructor
     *
     * @param   ExpressionInterface $innerException
     */
    public function __construct( ExpressionInterface $innerException = null )
    {
        if ( $innerException )
        {
            $this->setInnerExpression( $innerException );
        }
    }

    /**
     * Set inner expression
     *
     * @param   ExpressionInterface $innerException
     * @return  NotExpression
     */
    public function setInnerExpression( ExpressionInterface $innerException )
    {
        $this->innerException = $innerException;
        return $this;
    }

    /**
     * Get inner expression
     *
     * @return  ExpressionInterface
     */
    public function getInnerExpression()
    {
        return $this->innerException;
    }

    /**
     * @return  bool
     */
    public function isEmpty()
    {
        return $this->innerException->isEmpty();
    }

    /**
     * @return  string
     */
    public function toQueryString()
    {
        return '!' . $this->innerException->toQueryString();
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        return '-' . $this->innerException->toRepresentation();
    }

}
