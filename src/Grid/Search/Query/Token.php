<?php

namespace Grid\Search\Query;

use Normalizer;

/**
 * Token
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Token
{

    /**
     * @const string
     */
    const T_LEXEME = 'T_LEXEME';

    /**
     * @const string
     */
    const T_LEXEME_PREFIX = 'T_LEXEME_PREFIX';

    /**
     * @const string
     */
    const T_PHRASE = 'T_PHRASE';

    /**
     * @const string
     */
    const T_OPERATOR_NOT = 'T_OPERATOR_NOT';

    /**
     * @const string
     */
    const T_OPERATOR_OR = 'T_OPERATOR_OR';

    /**
     * @const string
     */
    const T_OPERATOR_AND = 'T_OPERATOR_AND';

    /**
     * @const string
     */
    const T_SET_OPEN = 'T_SET_OPEN';

    /**
     * @const string
     */
    const T_SET_CLOSE = 'T_SET_CLOSE';

    /**
     * @const string
     */
    const LEXEME_LETTERS = 'a-zA-Z0-9\\pL\\pM\\pN';

    /**
     * @const string
     */
    const CONTROL_LETTERS = '\'"!-|&()';

    /**
     * @const string
     */
    const NORMALIZATION_FORM = Normalizer::FORM_C;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $data;

    /**
     * Constructor
     *
     * @param   string  $data
     * @param   string  $type
     */
    public function __construct( $data = '', $type = self::T_LEXEME )
    {
        $this->data = (string) $data;
        $this->type = (string) $type;
    }

    /**
     * Lexer
     *
     * @param   string  $query
     * @return  Token[]
     */
    public static function lexer( $query )
    {
        $tokens = array();
        $query  = (string) $query;

        if ( ! Normalizer::isNormalized( $query, static::NORMALIZATION_FORM ) )
        {
            $query = Normalizer::normalize( $query, static::NORMALIZATION_FORM );
        }

        $query = static::skipWhiteSpace( $query );

        while ( ! empty( $query ) )
        {
            switch ( $query[0] )
            {
                case '|':
                    $tokens[] = new static( $query[0], static::T_OPERATOR_OR );
                    $query    = substr( $query, 1 );
                    break;

                case '&':
                    $tokens[] = new static( $query[0], static::T_OPERATOR_AND );
                    $query    = substr( $query, 1 );
                    break;

                case '-':
                case '!':
                    $tokens[] = new static( $query[0], static::T_OPERATOR_NOT );
                    $query    = substr( $query, 1 );
                    break;

                case '(':
                    $tokens[] = new static( $query[0], static::T_SET_OPEN );
                    $query    = substr( $query, 1 );
                    break;

                case ')':
                    $tokens[] = new static( $query[0], static::T_SET_CLOSE );
                    $query    = substr( $query, 1 );
                    break;

                case '\'':
                case '"':
                    $until    = $query[0];
                    $query    = substr( $query, 1 );
                    $phrase   = '';

                    while ( ! empty( $query ) && $query[0] != $until )
                    {
                        $part = strstr( $query, $until, true );

                        if ( $part === false )
                        {
                            $phrase .= $query;
                            $query   = $until;
                            break;
                        }

                        $phrase .= $part;
                        $query   = substr( $query, strlen( $part ) );

                        if ( $query[0] === $until && isset( $query[1] ) && $query[1] === $until )
                        {
                            $phrase .= $until;
                            $query   = substr( $query, 2 );
                        }
                    }

                    $tokens[] = new static( $phrase, static::T_PHRASE );
                    $query    = substr( $query, 1 );
                    break;

                default:
                    $matches = array();

                    if ( preg_match( '/^[' . static::LEXEME_LETTERS . '\']+/u', $query, $matches ) )
                    {
                        $match = $matches[0];
                        $query = substr( $query, strlen( $match ) );

                        if ( false === strpos( $match, '\'' ) )
                        {
                            $tokens[] = new static( $match, static::T_LEXEME );

                            if ( '*' == $query[0] )
                            {
                                $tokens[] = new static( '*', static::T_LEXEME_PREFIX );
                                $query    = substr( $query, 1 );
                            }
                            else if ( ':' == $query[0] && '*' == $query[1] )
                            {
                                $tokens[] = new static( ':*', static::T_LEXEME_PREFIX );
                                $query    = substr( $query, 2 );
                            }
                        }
                        else
                        {
                            $tokens[] = new static( $match, static::T_PHRASE );
                        }
                    }
                    else
                    {
                        break 2;
                    }
            }

            $query = static::skipWhiteSpace( $query );
        }

        return $tokens;
    }

    /**
     * Skip white-space
     *
     * @param   string  $query
     * @return  string
     */
    protected static function skipWhiteSpace( $query )
    {
        static $regexp = null;

        if ( empty( $regexp ) )
        {
            $regexp = '/^[^'
                    . preg_quote( static::CONTROL_LETTERS, '/' )
                    . static::LEXEME_LETTERS
                    . ']+/u';
        }

        return preg_replace( $regexp, '', $query );
    }

}
