<?php

namespace Grid\Search\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * OpenSearchController
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class OpenSearchController extends AbstractSearchController
{

    /**
     * Get suggestions' data
     *
     * @return  array
     */
    protected function getSuggestionsData()
    {
        static $defaultPorts = array(
            'http'  => 80,
            'https' => 443,
        );

        $links  = array();
        $url    = $this->url();
        $locale = (string) $this->locale();
        $info   = $this->getServiceLocator()
                       ->get( 'SiteInfo' );
        $base   = $info->getSubdomainUrl( '', '/' );

        list( $suggestions,
              $query,
              $type,
              $all ) = $this->getSuggestions();

        foreach ( $suggestions as $suggestion )
        {
            $links[] = $base . $url->fromRoute(
                'Grid\Search\Search\Index',
                array(
                    'locale'    => $locale,
                ),
                array(
                    'query'     => array(
                        'query' => $suggestion,
                        'type'  => $type,
                        'all'   => $all,
                    ),
                )
            );
        }

        return array(
            $query,
            $suggestions,
            $suggestions,
            $links
        );
    }

    /**
     * Get view-model
     *
     * @param   string  $mimeType
     * @param   array   $params
     * @return  ViewModel
     */
    protected function getViewModel( $mimeType, array $params = array() )
    {
        $view = new ViewModel( $params );

        $this->getResponse()
             ->getHeaders()
             ->addHeaderLine( 'Content-Type', $mimeType );

        return $view->setTerminal( true );
    }

    /**
     * OpenSearch atom (search & return in atom format) action
     */
    public function atomAction()
    {
        return $this->getViewModel(
            'application/atom+xml',
            $this->getResults()
        );
    }

    /**
     * OpenSearch rss (search & return in rss format) action
     */
    public function rssAction()
    {
        return $this->getViewModel(
            'application/rss+xml',
            $this->getResults()
        );
    }

    /**
     * Search suggestions-json action
     */
    public function suggestionsJsonAction()
    {
        return new JsonModel( $this->getSuggestionsData() );
    }

    /**
     * Search suggestions-xml action
     */
    public function suggestionsXmlAction()
    {
        list( $query,
              $suggestions,
              $descriptions,
              $links ) = $this->getSuggestionsData();

        return $this->getViewModel( 'application/x-suggestions+xml', array(
            'query'         => $query,
            'suggestions'   => $suggestions,
            'descriptions'  => $descriptions,
            'links'         => $links,
        ) );
    }

    /**
     * OpenSearch description action
     */
    public function descriptionAction()
    {
        /* @var $mailService \Zork\Mail\Service */
        $serviceLocator = $this->getServiceLocator();
        $mailService    = $serviceLocator->get( 'Zork\Mail\Service' );
        $defaultReplyTo = $mailService->getDefaultReplyTo();
        $defaultFrom    = $mailService->getDefaultFrom();

        if ( ! empty( $defaultReplyTo['email'] ) )
        {
            $contact = $defaultReplyTo['email'];
        }
        else if ( ! empty( $defaultFrom['email'] ) )
        {
            $contact = $defaultFrom['email'];
        }
        else
        {
            $contact = null;
        }

        return $this->getViewModel(
            'application/opensearchdescription+xml',
            array(
                'contact' => $contact,
            )
        );
    }

}
