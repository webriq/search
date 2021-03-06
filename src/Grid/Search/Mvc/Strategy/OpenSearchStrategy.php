<?php

namespace Grid\Search\Mvc\Strategy;

use Locale;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * OpenSearchStrategy
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class OpenSearchStrategy implements ListenerAggregateInterface
{

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param   EventManagerInterface   $events
     * @param   int                     $priority
     * @return  void
     */
    public function attach( EventManagerInterface $events )
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            array( $this, 'appendOpenSearchToHeadLink' ),
            -1000
        );
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param   EventManagerInterface   $events
     * @return  void
     */
    public function detach( EventManagerInterface $events )
    {
        foreach ( $this->listeners as $index => $listener )
        {
            if ( $events->detach( $listener ) )
            {
                unset( $this->listeners[$index] );
            }
        }
    }

    /**
     * Locale to ISO
     *
     * @param   string  $locale
     * @return  string
     */
    protected static function localeToIso( $locale )
    {
        if ( empty( $locale ) )
        {
            return '';
        }

        $parsed = Locale::parseLocale( $locale );

        // @codeCoverageIgnoreStart
        if ( empty( $parsed ) ||
             empty( $parsed['language'] ) )
        {
            return $locale;
        }
        // @codeCoverageIgnoreEnd

        $result = strtolower( $parsed['language'] );

        if ( ! empty( $parsed['region'] ) )
        {
            $result .= '-' . strtoupper( $parsed['region'] );
        }

        return $result;
    }

    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param   ViewEvent   $event
     * @return  void
     */
    public function appendOpenSearchToHeadLink( MvcEvent $event )
    {
        /* @var $app \Zend\Mvc\Application */
        $app      = $event->getParam( 'application' );
        $request  = $app->getRequest();

        if ( ! $request instanceof HttpRequest )
        {
            return;
        }

        /* @var $renderer \Zend\View\Renderer\PhpRenderer */
        $locator  = $app->getServiceManager();
        $renderer = $locator->get( 'Zend\View\Renderer\PhpRenderer' );

        if ( method_exists( $renderer, 'plugin' ) )
        {
            /* @var $locale \Zork\I18n\Locale\Locale */
            /* @var $siteInfo \Zork\Db\SiteInfo */
            /* @var $headLink \Zend\View\Helper\HeadLink */
            /* @var $headTitle \Zork\View\Helper\HeadTitle */
            $locale     = $locator->get( 'Locale' );
            $siteInfo   = $locator->get( 'SiteInfo' );
            $headLink   = $renderer->plugin( 'headLink' );
            $headTitle  = $renderer->plugin( 'headTitle' );
            $title      = $headTitle->slice( 0, 1 );
            $current    = $locale->getCurrent();
            $href       = $siteInfo->getSubdomainUrl(
                '', '/app/%s/search/opensearch/description.xml'
            );

            $headLink->append( (object) array(
                'type'      => 'application/opensearchdescription+xml',
                'rel'       => 'search',
                'title'     => $title,
                'href'      => sprintf( $href, $current ),
                'hreflang'  => static::localeToIso( $current ),
            ) );

            foreach ( $locale->getAvailableLocales() as $availableLocale )
            {
                if ( $availableLocale != $current )
                {
                    $headLink->append( (object) array(
                        'type'      => 'application/opensearchdescription+xml',
                        'rel'       => 'search',
                        'title'     => $title,
                        'href'      => sprintf( $href, $availableLocale ),
                        'hreflang'  => static::localeToIso( $availableLocale ),
                    ) );
                }
            }
        }
    }

}
