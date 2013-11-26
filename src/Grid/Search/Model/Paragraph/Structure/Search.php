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
     * Get search form
     *
     * @param   string  $action
     * @return  \Zend\Form\Form
     */
    public function getForm( $action )
    {
        $form = $this->getServiceLocator()
                     ->get( 'Form' )
                     ->create( 'Grid\Search\Paragraph' );

        $form->setAttribute( 'action', $action );
        return $form;
    }

}
