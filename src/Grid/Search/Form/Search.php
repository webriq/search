<?php

namespace Grid\Search\Form;

use Zork\Form\Form;
use Zork\Form\PrepareElementsAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Search form(s)
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Search extends Form
          implements ServiceLocatorAwareInterface,
                     PrepareElementsAwareInterface
{

    use ServiceLocatorAwareTrait;

    /**
     * @var bool
     */
    private $multiLanguage;

    /**
     * Is in multi-languge mode?
     *
     * @return  bool|null
     */
    protected function isMultiLanguage()
    {
        if ( null === $this->multiLanguage &&
             $serviceLocator = $this->getServiceLocator() )
        {
            try
            {
                $this->multiLanguage = 1 < count(
                    $serviceLocator->get( 'locale' )
                                   ->getAvailableLocales()
                );
            }
            catch ( ServiceNotFoundException $ex )
            {
                $this->multiLanguage = null;
            }
        }

        return $this->multiLanguage;
    }

    /**
     * Prepare elements for the form
     *
     * @return void
     */
    public function prepareElements()
    {
        if ( false === $this->isMultiLanguage() )
        {
            $this->remove( 'all' );
        }
    }

}
