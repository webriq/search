<?php

namespace Grid\Search\Model\Result;

use Zork\Stdlib\OptionsTrait;

/**
 * Search options
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Options
{

    use OptionsTrait;

    /**
     * @const int
     */
    const NORMALIZATION_IGNORE_DOCUMENT_LENGTH = 0;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_LOGARITHM_OF_DOCUMENT_LENGTH = 1;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_DOCUMENT_LENGTH = 2;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_MEAN_HARMONIC_DISTANCE = 4;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_UNIQUE_WORDS = 8;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_LOGARITHM_OF_UNIQUE_WORDS = 16;

    /**
     * @const int
     */
    const NORMALIZATION_DIVIDE_BY_SELF_PLUS_ONE = 32;

    /**
     * @const int
     */
    const NORMALIZATION_DEFAULT = 36; // 4 | 32

    /**
     * @var bool
     */
    protected $coverDensity = true;

    /**
     * @var float
     */
    protected $weightTitle = 1.0;

    /**
     * @var float
     */
    protected $weightKeywords = 0.4;

    /**
     * @var float
     */
    protected $weightDescription = 0.2;

    /**
     * @var float
     */
    protected $weightContent = 0.1;

    /**
     * @var int
     */
    protected $normalization = self::NORMALIZATION_DEFAULT;

    /**
     * @var string
     */
    protected $startSelection = '<mark>';

    /**
     * @var string
     */
    protected $stopSelection = '</mark>';

    /**
     * @var int
     */
    protected $maxWords = 35;

    /**
     * @var int
     */
    protected $minWords = 15;

    /**
     * @var int
     */
    protected $shortWord = 3;

    /**
     * @var int
     */
    protected $maxFragments = 0;

    /**
     * @var string
     */
    protected $fragmentDelimiter = ' ... ';

    /**
     * Constructor
     *
     * @param   array|\Traversable  $options
     */
    public function __construct( $options = null )
    {
        if ( $options )
        {
            $this->setOptions( $options );
        }
    }

    /**
     * Set cover density
     *
     * @param   bool    $coverDensity
     * @return  Options
     */
    public function setCoverDensity( $coverDensity = true )
    {
        $this->coverDensity = (bool) $coverDensity;
        return $this;
    }

    /**
     * Set title weight
     *
     * @param   float   $weightTitle
     * @return  Options
     */
    public function setWeightTitle( $weightTitle )
    {
        $this->weightTitle = max( 0, min( 1, (float) $weightTitle ) );
        return $this;
    }

    /**
     * Set keywords weight
     *
     * @param   float   $weightKeywords
     * @return  Options
     */
    public function setWeightKeywords( $weightKeywords )
    {
        $this->weightKeywords = max( 0, min( 1, (float) $weightKeywords ) );
        return $this;
    }

    /**
     * Set description weight
     *
     * @param   float   $weightDescription
     * @return  Options
     */
    public function setWeightDescription( $weightDescription )
    {
        $this->weightDescription = max( 0, min( 1, (float) $weightDescription ) );
        return $this;
    }

    /**
     * Set content weight
     *
     * @param   float   $weightContent
     * @return  Options
     */
    public function setWeightContent( $weightContent )
    {
        $this->weightContent = max( 0, min( 1, (float) $weightContent ) );
        return $this;
    }

    /**
     * Set weights
     *
     * @param   array   $weights
     * @return  Options
     */
    public function setWeights( array $weights )
    {
        @ list( $title, $keywords, $description, $content ) = $weights;

        return $this->setWeightTitle( $title )
                    ->setWeightKeywords( $keywords )
                    ->setWeightDescription( $description )
                    ->setWeightContent( $content );
    }

    /**
     * Get weights
     *
     * @return  array
     */
    public function getWeights()
    {
        return array(
            $this->weightTitle,
            $this->weightKeywords,
            $this->weightDescription,
            $this->weightContent,
        );
    }

    /**
     * Set normalization
     *
     * @param   int|array   $normalization
     * @return  Options
     */
    public function setNormalization( $normalization )
    {
        if ( is_array( $normalization ) )
        {
            $normalization = array_reduce(
                array_keys( array_filter( $normalization ) ),
                function ( $result, $flag ) {
                    $result |= $flag;
                    return $result;
                },
                0
            );
        }

        $this->normalization = max( 0, $normalization );
        return $this;
    }

    /**
     * Set start selection
     *
     * @param   string  $startSelection
     * @return  Options
     */
    public function setStartSelection( $startSelection )
    {
        $this->startSelection = (string) $startSelection;
        return $this;
    }

    /**
     * Set stop selection
     *
     * @param   string  $stopSelection
     * @return  Options
     */
    public function setStopSelection( $stopSelection )
    {
        $this->stopSelection = (string) $stopSelection;
        return $this;
    }

    /**
     * Set selection tag
     *
     * @param   string  $selectionTag
     * @return  Options
     */
    public function setSelectionTag( $selectionTag )
    {
        return $this->setStartSelection( '<' . $selectionTag . '>' )
                    ->setStopSelection( '</' . $selectionTag . '>' );
    }

    /**
     * Set max words
     *
     * @param   int     $maxWords
     * @return  Options
     */
    public function setMaxWords( $maxWords )
    {
        $this->maxWords = max( 0, (int) $maxWords );
        return $this;
    }

    /**
     * Set min words
     *
     * @param   int     $maxWords
     * @return  Options
     */
    public function setMinWords( $minWords )
    {
        $this->minWords = max( 0, (int) $minWords );
        return $this;
    }

    /**
     * Get max words
     *
     * @return  int
     */
    public function getMaxWords()
    {
        return max( $this->minWords, $this->maxWords );
    }

    /**
     * Get min words
     *
     * @return  int
     */
    public function getMinWords()
    {
        return min( $this->minWords, $this->maxWords );
    }

    /**
     * Set short word
     *
     * @param   int     $shortWord
     * @return  Options
     */
    public function setShortWord( $shortWord )
    {
        $this->shortWord = max( 1, (int) $shortWord );
        return $this;
    }

    /**
     * Set max fragments
     *
     * @param   int     $maxFragments
     * @return  Options
     */
    public function setMaxFragments( $maxFragments )
    {
        $this->maxFragments = max( 0, (int) $maxFragments );
        return $this;
    }

    /**
     * Set fragment delimiter
     *
     * @param   string  $fragmentDelimiter
     * @return  Options
     */
    public function setFragmentDelimiter( $fragmentDelimiter )
    {
        $this->fragmentDelimiter = ' ' . trim( (string) $fragmentDelimiter ) . ' ';
        return $this;
    }

}
