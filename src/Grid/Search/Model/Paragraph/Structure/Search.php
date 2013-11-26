<?php

namespace Grid\Search\Model\Paragraph\Structure;

use Grid\Paragraph\Model\Paragraph\Structure\AbstractLeaf;

/**
 * Search paragraph
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Search extends AbstractLeaf
{

    /**
     * Paragraph type
     *
     * @var string
     */
    protected static $type = 'search';

    /**
     * Paragraph-render view-open
     *
     * @var string
     */
    protected static $viewOpen = 'grid/paragraph/render/search';

    /**
     * Display the "Install this search" button or not
     *
     * @var bool
     */
    protected $displayInstallOpensearch = false;

    /**
     * Set sisplay the "Install this search" button or not
     *
     * @param   bool    $display
     * @return  Search
     */
    public function setDisplayInstallOpensearch( $display )
    {
        $this->displayInstallOpensearch = (bool) $display;
        return $this;
    }

    /**
     * Get search form
     *
     * @param   string  $action
     * @return  \Zork\Form\Form
     */
    public function getForm( $action )
    {
        /* @var $form \Zork\Form\Form */
        $form = $this->getServiceLocator()
                     ->get( 'Form' )
                     ->create( 'Grid\Search\Paragraph' );

        if ( ! $this->displayInstallOpensearch )
        {
            /* @var $submit \Zork\Form\Element\Submit */
            $submit = $form->get( 'submit' );
            $types  = array_filter(
                preg_split(
                    '/\s+/',
                    trim( $submit->getAttribute( 'data-js-type' ) )
                ),
                function ( $type )
                {
                    return ! in_array( $type, array(
                        'js.search.submit',
                        'zork.search.submit',
                    ) );
                }
            );

            $submit->setAttribute(
                'data-js-type',
                empty( $types ) ? null : implode( ' ', $types )
            );
        }

        $form->setAttribute( 'action', $action );
        return $form;
    }

}
