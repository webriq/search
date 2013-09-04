<?php

namespace Grid\Search\Controller;

use Zend\View\Model\JsonModel;

/**
 * SearchController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class SearchController extends AbstractSearchController
{

    /**
     * Search index action
     */
    public function indexAction()
    {
        /* @var $form \Zend\Form\Form */
        /* @var $items \Zend\Form\Element\Select */
        $results = $this->getResults();
        $form    = $this->getServiceLocator()
                        ->get( 'Form' )
                        ->get( 'Grid\Search\Search' );
        $items   = $form->get( 'items' );
        $options = $items->getValueOptions();
        $hasit   = false;

        $this->paragraphLayout();

        foreach ( $options as $option )
        {
            if ( is_array( $option ) )
            {
                $hasit = isset( $option['value'] ) && $option['value'] == $results['items'];
            }
            else if ( is_numeric( $option ) )
            {
                $hasit = $option == $results['items'];
            }

            if ( $hasit )
            {
                break;
            }
        }

        if ( ! $hasit )
        {
            $options[] = array(
                'label' => $results['items'],
                'value' => $results['items'],
            );

            $items->setValueOptions( $options );
        }

        $form->setData( array(
            'items' => $results['items'],
            'query' => $results['query'],
            'type'  => $results['type'],
            'all'   => $results['all'],
        ) );

        $results['form'] = $form;
        return $results;
    }

    /**
     * Search autocomplete action
     */
    public function autocompleteAction()
    {
        list( $suggestions ) = $this->getSuggestions();
        return new JsonModel( $suggestions );
    }

}
