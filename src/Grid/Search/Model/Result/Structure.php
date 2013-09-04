<?php

namespace Grid\Search\Model\Result;

use Zork\Model\MapperAwareTrait;
use Zork\Model\MapperAwareInterface;
use Zork\Model\Structure\StructureAbstract;

/**
 * Structure
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Structure extends StructureAbstract implements MapperAwareInterface
{

    use MapperAwareTrait
    {
        MapperAwareTrait::__clone as protected cloneMapper;
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $keywords;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $headline;

    /**
     * @var float
     */
    protected $rank;

    /**
     * @return  string
     */
    public function getUrl()
    {
        // TODO
        return '#error';
    }

}
