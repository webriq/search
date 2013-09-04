<?php

namespace Grid\Search\Query;

/**
 * Lexeme
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Lexeme extends AbstractData
{

    /**
     * @var bool
     */
    protected $isPrefix = false;

    /**
     * Constructor
     *
     * @param type $data
     * @param type $isPrefix
     */
    public function __construct( $data = null, $isPrefix = null )
    {
        parent::__construct( $data );

        if ( null !== $isPrefix )
        {
            $this->setIsPrefix( $isPrefix );
        }
    }

    /**
     * Set is prefix
     *
     * @param   bool    $isPrefix
     * @return  Lexeme
     */
    public function setIsPrefix( $isPrefix = true )
    {
        $this->isPrefix = (bool) $isPrefix;
        return $this;
    }

    /**
     * Get is prefix
     *
     * @return  bool
     */
    public function isPrefix()
    {
        return $this->isPrefix;
    }

    /**
     * @return  string
     */
    public function toQueryString()
    {
        return parent::toQueryString() . ( $this->isPrefix ? ':*' : '' );
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        return parent::toRepresentation() . ( $this->isPrefix ? '*' : '' );
    }

}
