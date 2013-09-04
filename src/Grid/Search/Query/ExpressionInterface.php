<?php

namespace Grid\Search\Query;

/**
 * ExpressionInterface
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
interface ExpressionInterface
{

    /**
     * @return  bool
     */
    public function isEmpty();

    /**
     * @return  string
     */
    public function toQueryString();

    /**
     * @return  string
     */
    public function toRepresentation();

}
