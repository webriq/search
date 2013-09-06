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
    const OP_OR = 'OR';

    /**
     * @const string
     */
    const OP_AND = 'AND';

    /**
     * @const string
     */
    const OP_DEFAULT = self::OP_OR;

    /**
     * @const string
     */
    const OPEN = '(';

    /**
     * @const string
     */
    const CLOSE = ')';

    /**
     * @var array
     */
    protected $operators = array();

    /**
     * @var array
     */
    protected $expressions = array();

    /**
     * @var array
     */
    protected $operatorToQueryString = array(
        self::OP_OR   => ' | ',
        self::OP_AND  => ' & ',
    );

    /**
     * @var array
     */
    protected $operatorToRepresentation = array(
        self::OP_OR   => ' ',
        self::OP_AND  => ' & ',
    );

    /**
     * Constructor
     *
     * @param   array|\Traversable  $expressions
     * @param   string              $operator
     */
    public function __construct( $expressions = null, $operator = self::OP_DEFAULT )
    {
        if ( $expressions )
        {
            $this->appendExpressions( $expressions, $operator );
        }
    }

    /**
     * Add expression
     *
     * @param   ExpressionInterface $expression
     * @param   string              $operator
     * @return  ExpressionSet
     */
    public function appendExpression( ExpressionInterface $expression, $operator = self::OP_DEFAULT )
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

        if ( static::OP_AND !== $operator || static::OP_OR !== $operator )
        {
            $operator = static::OP_DEFAULT;
        }

        $this->operators[]      = $operator;
        $this->expressions[]    = $expression;
        return $this;
    }

    /**
     * Add expressions
     *
     * @param   array|\Traversable  $expressions
     * @param   string              $operator
     * @return  ExpressionSet
     */
    public function appendExpressions( $expressions, $operator = self::OP_DEFAULT )
    {
        foreach ( $expressions as $expression )
        {
            if ( $expression instanceof ExpressionInterface )
            {
                $this->appendExpression( $expression, $operator );
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
        $this->operators    = array();
        $this->expressions  = array();
        return $this;
    }

    /**
     * Set expressions
     *
     * @param   array|\Traversable  $expressions
     * @return  ExpressionSet
     */
    public function setExpressions( $expressions, $operator = self::OP_DEFAULT )
    {
        return $this->clearExpressions()
                    ->appendExpressions( $expressions, $operator );
    }

    /**
     * Set expressions from another expression-set
     *
     * @param   ExpressionSet $expressionSet
     * @return  ExpressionSet
     */
    public function setExpressionsFrom( ExpressionSet $expressionSet )
    {
        $this->operators    = $expressionSet->operators;
        $this->expressions  = $expressionSet->expressions;
        return $this;
    }

    /**
     * @return  bool
     */
    public function isEmpty()
    {
        return empty( $this->expressions );
    }

    /**
     * @return  string
     */
    public function toQueryString()
    {
        $result = '';

        foreach ( $this->expressions as $index => $expression )
        {
            if ( $expression->isEmpty() )
            {
                continue;
            }

            if ( $result )
            {
                $result .= $this->operatorToQueryString[
                    $this->operators[$index]
                ];
            }

            $result .= $expression->toQueryString();
        }

        return static::OPEN . $result . static::CLOSE;
    }

    /**
     * @return  string
     */
    public function toRepresentation()
    {
        $result = '';

        foreach ( $this->expressions as $index => $expression )
        {
            if ( $expression->isEmpty() )
            {
                continue;
            }

            if ( $result )
            {
                $result .= $this->operatorToRepresentation[
                    $this->operators[$index]
                ];
            }

            $result .= $expression->toRepresentation();
        }

        return static::OPEN . $result . static::CLOSE;
    }

}
