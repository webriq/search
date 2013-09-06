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
            case Token::T_OPERATOR_AND:
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
        $expressionSet = new ExpressionSet();
        $nextOperator  = ExpressionSet::OP_DEFAULT;

        while ( ! empty( $tokens ) )
        {
            $expression = static::acceptExpression( $tokens );

            if ( ! empty( $expression ) )
            {
                $expressionSet->appendExpression( $expression, $nextOperator );

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
                            $nextOperator = ExpressionSet::OP_OR;
                            break;

                        case Token::T_OPERATOR_AND:
                            $nextOperator = ExpressionSet::OP_AND;
                            break;

                        default:
                            array_unshift( $tokens, $nextToken );
                            $nextOperator = ExpressionSet::OP_DEFAULT;
                            break;
                    }
                }
            }
        }

        return $expressionSet;
    }

}
