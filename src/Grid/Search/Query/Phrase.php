<?php

namespace Grid\Search\Query;

/**
 * Phrase
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Phrase extends AbstractData
{

    /**
     * @return  string
     */
    public function toQueryString()
    {
        return '\'' . str_replace( '\'', '\'\'', parent::toQueryString() ) . '\'';
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        return '"' . str_replace( '"', '""', parent::toRepresentation() ) . '"';
    }

}
