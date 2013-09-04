<?php

namespace Grid\Search\Query;

/**
 * Parser
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
abstract class Parser
{

    /**
     * Parse query string
     *
     * @param   string  $query
     * @param   bool    $encapsulateInSet
     * @return  ExpressionRoot
     */
    public static function parse( $query )
    {
        $tokens     = Token::lexer( $query );
        $expression = static::acceptExpressionSet( $tokens, true );

        if ( empty( $expression ) )
        {
            return new ExpressionRoot();
        }

        $expressions = $expression->getExpressions();

        if ( count( $expressions ) === 1 )
        {
            $innerExpression = reset( $expressions );

            if ( $innerExpression instanceof ExpressionSet )
            {
                $expression = $innerExpression;
            }
        }

        return new ExpressionRoot(
            $expression->getExpressions(),
            $expression->getOperator()
        );
    }

    /**
     * Accept any expression
     *
     * @param   string  $query
     * @return  ExpressionInterface
     */
    protected static function acceptExpression( array &$tokens )
    {
        if ( empty( $tokens ) )
        {
            return null;
        }

        $token      = array_shift( $tokens );
        $expression = null;

        switch ( $token->type )
        {
            case Token::T_LEXEME:
                $expression = new Lexeme( $token->data );

                if ( ! empty( $tokens ) )
                {
                    $nextToken = array_shift( $tokens );

                    if ( Token::T_LEXEME_PREFIX === $nextToken->type )
                    {
                        $expression->setIsPrefix();
                    }
                    else
                    {
                        array_unshift( $tokens, $nextToken );
                    }
                }

                break;

            case Token::T_PHRASE:
                $expression = new Phrase( trim(
                    preg_replace( '/\\s+/', ' ', $token->data )
                ) );
                break;

            case Token::T_OPERATOR_NOT:
                $innerExpression = static::acceptExpression( $tokens );

                if ( $innerExpression )
                {
                    $expression = new NotExpression( $innerExpression );
                }
                break;

            case Token::T_SET_OPEN:
                if ( empty( $tokens ) )
                {
                    break;
                }

                $expression = static::acceptExpressionSet( $tokens );
                $expressions = $expression->getExpressions();

                if ( count( $expressions ) === 1 )
                {
                    $expression = reset( $expressions );
                }
                break;

            case Token::T_OPERATOR_OR:
            case Token::T_LEXEME_PREFIX:
            case Token::T_SET_CLOSE:
            default:
                // skip
                break;
        }

        return $expression;
    }

    /**
     * Accept expression set
     *
     * @param   array   $tokens
     * @param   bool    $skipClose
     * @return  null|ExpressionSet
     */
    protected static function acceptExpressionSet( array &$tokens, $skipClose = false )
    {
        $entities = array(); // can be expression, or operator

        while ( ! empty( $tokens ) )
        {
            $expression = static::acceptExpression( $tokens );

            if ( ! empty( $expression ) )
            {
                $entities[] = $expression;

                if ( ! empty( $tokens ) )
                {
                    $nextToken = array_shift( $tokens );

                    switch ( $nextToken->type )
                    {
                        case Token::T_SET_CLOSE:
                            if ( ! $skipClose )
                            {
                                break 2;
                            }
                            break;

                        case Token::T_OPERATOR_OR:
                            $entities[] = ExpressionSet::OP_OR;
                            break;

                        default: // AND
                            array_unshift( $tokens, $nextToken );
                            $entities[] = ExpressionSet::OP_AND;
                            break;
                    }
                }
            }
        }

        if ( in_array( end( $entities ),
                       array( ExpressionSet::OP_OR,
                              ExpressionSet::OP_AND ) ) )
        {
            array_pop( $entities );
        }

        if ( empty( $entities ) )
        {
            return null;
        }

        if ( in_array( ExpressionSet::OP_OR, $entities ) )
        {
            $expression = new ExpressionSet( array(), ExpressionSet::OP_OR );

            if ( in_array( ExpressionSet::OP_AND, $entities ) )
            {
                $expressions = array();
                $operator    = ExpressionSet::OP_OR;
                $entities[]  = ExpressionSet::OP_OR;

                while ( ! empty( $entities ) )
                {
                    $currentExpression = array_shift( $entities );

                    switch ( $operator )
                    {
                        case ExpressionSet::OP_OR:
                            $expressions[] = $currentExpression;
                            break;

                        case ExpressionSet::OP_AND:
                            $lastExpression = array_pop( $expressions );

                            if ( ! $lastExpression instanceof ExpressionSet ||
                                 ExpressionSet::OP_AND !== $lastExpression->getOperator() )
                            {
                                $lastExpression = new ExpressionSet(
                                    array( $lastExpression ),
                                    ExpressionSet::OP_AND
                                );
                            }

                            $lastExpression->addExpression( $currentExpression );
                            $expressions[] = $lastExpression;
                            break;

                        default:
                            break 2;
                    }

                    $operator = array_shift( $entities );
                }

                $expression->addExpressions( $expressions );
            }
            else
            {
                $expression->addExpressions( $entities );
            }
        }
        else
        {
            $expression = new ExpressionSet(
                $entities,
                ExpressionSet::OP_AND
            );
        }

        return $expression;
    }

}
