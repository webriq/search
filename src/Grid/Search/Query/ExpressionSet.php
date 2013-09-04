<?php

namespace Grid\Search\Query;

/**
 * ExpressionSet
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class ExpressionSet implements ExpressionInterface
{

    /**
     * @const string
     */
    const OP_AND = 'AND';

    /**
     * @const string
     */
    const OP_OR = 'OR';

    /**
     * @const string
     */
    const OPEN = '(';

    /**
     * @const string
     */
    const CLOSE = ')';

    /**
     * @var string
     */
    protected $operator = self::OP_AND;

    /**
     * @var array
     */
    protected $expressions = array();

    /**
     * Constructor
     *
     * @param   array|\Traversable  $expressions
     * @param   string              $operator
     */
    public function __construct( $expressions = null, $operator = null )
    {
        if ( $expressions )
        {
            $this->addExpressions( $expressions );
        }

        if ( $operator )
        {
            $this->setOperator( $operator );
        }
    }

    /**
     * Add expression
     *
     * @param   ExpressionInterface $expression
     * @return  ExpressionSet
     */
    public function addExpression( ExpressionInterface $expression )
    {
        $this->expressions[] = $expression;
        return $this;
    }

    /**
     * Add expressions
     *
     * @param   array|\Traversable  $expressions
     * @return  ExpressionSet
     */
    public function addExpressions( $expressions )
    {
        foreach ( $expressions as $expression )
        {
            if ( $expression instanceof ExpressionInterface )
            {
                $this->addExpression( $expression );
            }
        }

        return $this;
    }

    /**
     * Clear expressions
     *
     * @return ExpressionSet
     */
    public function clearExpressions()
    {
        $this->expressions = array();
        return $this;
    }

    /**
     * Set expressions
     *
     * @param   array|\Traversable  $expressions
     * @return  ExpressionSet
     */
    public function setExpressions( $expressions )
    {
        return $this->clearExpressions()
                    ->addExpressions( $expressions );
    }

    /**
     * Get expressions
     *
     * @return  array
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * Set operator
     *
     * @param   string  $operator
     * @return  ExpressionSet
     */
    public function setOperator( $operator )
    {
        $operator = (string) $operator;

        if ( '&' == $operator || '&&' == $operator )
        {
            $operator = static::OP_AND;
        }

        if ( '|' == $operator || '||' == $operator )
        {
            $operator = static::OP_OR;
        }

        if ( static::OP_AND == $operator || static::OP_OR == $operator )
        {
            $this->operator = $operator;
        }

        return $this;
    }

    /**
     * Get operator
     *
     * @return  string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return  bool
     */
    public function isEmpty()
    {
        return empty( $this->expressions );
    }

    /**
     * Get non-empty expressions
     *
     * @return  array
     */
    protected function getNonEmptyExpressions()
    {
        return array_filter(
            $this->expressions,
            function ( ExpressionInterface $expression ) {
                return ! $expression->isEmpty();
            }
        );
    }

    /**
     * @return  string
     */
    public function toQueryString()
    {
        static $glues = array(
            self::OP_AND  => ' & ',
            self::OP_OR   => ' | ',
        );

        return static::OPEN . implode(
            $glues[$this->operator],
            array_map(
                function ( ExpressionInterface $expression ) {
                    return $expression->toQueryString();
                },
                $this->getNonEmptyExpressions()
            )
        ) . static::CLOSE;
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        static $glues = array(
            self::OP_AND  => ' ',
            self::OP_OR   => '|',
        );

        return static::OPEN . implode(
            $glues[$this->operator],
            array_map(
                function ( ExpressionInterface $expression ) {
                    return $expression->toRepresentation();
                },
                $this->getNonEmptyExpressions()
            )
        ) . static::CLOSE;
    }

}
