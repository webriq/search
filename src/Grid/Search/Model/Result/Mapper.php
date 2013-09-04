<?php

namespace Grid\Search\Model\Result;

use Traversable;
use Zork\Db\Sql\Sql;
use Zork\Db\Sql\FunctionCall;
use Zend\Stdlib\ArrayUtils;
use Zork\Stdlib\OptionsTrait;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zork\Model\MapperAwareInterface;
use Zork\Model\DbAdapterAwareTrait;
use Zork\Model\DbAdapterAwareInterface;
use Zork\Model\Mapper\DbAware\DbSchemaAwareInterface;
use Zork\Model\Mapper\ReadListMapperInterface;
use Zork\Iterator\CallbackMapIterator;
use Zend\Authentication\AuthenticationService;

/**
 * Mapper
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Mapper implements HydratorInterface,
                        DbAdapterAwareInterface,
                        DbSchemaAwareInterface,
                        ReadListMapperInterface
{

    use OptionsTrait,
        DbAdapterAwareTrait,
        DbSchemaAwareTrait;

    /**
     * Sql-object
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     * @var Options
     */
    protected $searchOptions;

    /**
     * Structure prototype for the mapper
     *
     * @var Structure
     */
    protected $structurePrototype;

    /**
     * Get search options
     *
     * @return  Options
     */
    public function getSearchOptions()
    {
        if ( null === $this->searchOptions )
        {
            $this->searchOptions = new Options;
        }

        return $this->searchOptions;
    }

    /**
     * Set search options
     *
     * @param   Options     $searchOptions
     * @return  Mapper
     */
    public function setSearchOptions( Options $searchOptions )
    {
        $this->searchOptions = $searchOptions;
        return $this;
    }

    /**
     * Get structure prototype
     *
     * @return  Structure
     */
    public function getStructurePrototype()
    {
        return $this->structurePrototype;
    }

    /**
     * Get a Zend\Db\Sql\Sql object
     *
     * @return  \Zork\Db\Sql\Sql
     */
    protected function sql()
    {
        if ( null === $this->sql )
        {
            $this->sql = new Sql( $this->getDbAdapter() );
        }

        return $this->sql;
    }

    /**
     * Get schema qualified name
     *
     * @param   string      $name
     * @param   string|null $dbSchema
     * @return  string|array
     */
    protected function getSchemaQualifiedName( $name, $dbSchema = null )
    {
        if ( null === $dbSchema )
        {
            $dbSchema = $this->dbSchema;
        }

        if ( $dbSchema )
        {
            return array( $dbSchema, $name );
        }

        return $name;
    }

    /**
     * Set structure prototype
     *
     * @param   Structure $structurePrototype
     * @return  Mapper
     */
    public function setStructurePrototype( Structure $structurePrototype )
    {
        if ( $structurePrototype instanceof MapperAwareInterface )
        {
            $structurePrototype->setMapper( $this );
        }

        $this->structurePrototype = $structurePrototype;
        return $this;
    }

    /**
     * Constructor
     *
     * @param   Options     $searchOptions
     * @param   Structure   $structurePrototype
     */
    public function __construct( Options $searchOptions = null, Structure $structurePrototype = null )
    {
        $this->setSearchOptions( $searchOptions ?: new Options )
             ->setStructurePrototype( $structurePrototype ?: new Structure );
    }

    /**
     * Create structure from plain data
     *
     * @param   array   $data
     * @return  Structure
     */
    protected function createStructure( array $data )
    {
        $structure = clone $this->structurePrototype;
        $structure->setOptions( $data );

        if ( $structure instanceof MapperAwareInterface )
        {
            $structure->setMapper( $this );
        }

        return $structure;
    }

    /**
     * Get user id & group id
     *
     * @return  array
     */
    protected function getUserIdAndGroupId()
    {
        $auth = new AuthenticationService();

        if ( $auth->hasIdentity() )
        {
            /* @var $identity \Grid\User\Model\User\Structure */
            $identity = $auth->getIdentity();

            return array(
                $identity->id,
                $identity->groupId,
            );
        }

        return array( null, null );
    }

    /**
     * Extract values from an object
     *
     * @param   object  $structure
     * @return  array
     */
    public function extract( $structure )
    {
        if ( $structure instanceof Structure )
        {
            return $structure->toArray();
        }

        if ( $structure instanceof Traversable )
        {
            return ArrayUtils::iteratorToArray( $structure );
        }

        return (array) $structure;
    }

    /**
     * Hydrate $structure with the provided $data.
     *
     * @param   array   $data
     * @param   object  $structure
     * @return  object
     */
    public function hydrate( array $data, $structure )
    {
        if ( $structure instanceof Structure )
        {
            $structure->setOptions( $data );
        }
        else
        {
            foreach ( $data as $key => $value )
            {
                $structure->$key = $value;
            }
        }

        if ( $structure instanceof MapperAwareInterface )
        {
            $structure->setMapper( $this );
        }

        return $structure;
    }

    /**
     * Find a structure
     *
     * @param   string|array    $where
     * @return  Structure
     */
    public function find( $where )
    {
        return $this->findOne( $where );
    }

    /**
     * Find one structure
     *
     * @param   mixed|null  $where
     * @param   mixed|null  $options
     * @return  Structure
     */
    public function findOne( $where = null, $options = null )
    {
        foreach ( $this->findAll( $where, $options, 1, 0 ) as $structure )
        {
            return $structure;
        }

        return null;
    }

    /**
     * Parse where
     *
     * @param   mixed   $where
     * @return  array
     */
    protected function parseWhere( $where )
    {
        if ( empty( $where ) )
        {
            return array( null, null, null, null );
        }

        if ( $where instanceof Traversable )
        {
            $where = ArrayUtils::iteratorToArray( $where );
        }
        else if ( is_scalar( $where ) )
        {
            $where = array(
                'query' => (string) $where,
            );
        }
        else
        {
            $where = (array) $where;
        }

        if ( isset( $where['query'] ) )
        {
            $locale = isset( $where['locale'] ) ? (string) $where['locale'] : '';
            $query  = (string) $where['query'];
            $type   = isset( $where['type'] )   ? (string) $where['type']   : '%';
            $all    = isset( $where['all'] )    ? (bool)   $where['all']    : false;
        }
        else
        {
            @ list( $locale, $query, $type, $all ) = $where;
            $locale = isset( $locale )  ? (string) $locale  : '';
            $query  = isset( $query )   ? (string) $query   : '';
            $type   = isset( $type )    ? (string) $type    : '%';
            $all    = isset( $all )     ? (bool) $all       : false;
        }

        return array(
            $locale,
            $query,
            $type,
            $all,
        );
    }

    /**
     * Parse options
     *
     * @param   mixed   $options
     * @return  Options
     */
    protected function parseOptions( $options )
    {
        if ( $options instanceof Options )
        {
            return $options;
        }

        if ( empty( $options ) )
        {
            return $this->getSearchOptions();
        }

        return new Options( $options );
    }

    /**
     * Find multiple structures' count
     *
     * @param   mixed|null  $where
     * @return  int
     */
    public function findCount( $where = null )
    {
        list( $locale, $query, $type, $all ) = $this->parseWhere( $where );

        if ( empty( $query ) )
        {
            return 0;
        }

        list( $userId, $groupId ) = $this->getUserIdAndGroupId();

        return $this->sql()
                    ->call(
                        $this->getSchemaQualifiedName( 'search_result_count' ),
                        array(
                            (string) $locale,
                            (string) $query,
                            (string) $type,
                            (bool)   $all,
                            (int)    $userId,
                            (int)    $groupId,
                        )
                    );
    }

    /**
     * Find multiple structures
     *
     * @param   mixed|null  $where
     * @param   mixed|null  $options
     * @param   int|null    $limit
     * @param   int|null    $offset
     * @return  Structure[]
     */
    public function findAll( $where = null, $options = null, $limit = null, $offset = null )
    {
        list( $locale, $query, $type, $all ) = $this->parseWhere( $where );

        if ( empty( $query ) )
        {
            return array();
        }

        list( $userId, $groupId ) = $this->getUserIdAndGroupId();

        $options = $this->parseOptions( $options );
        $result  = $this->sql()
                        ->call(
                            $this->getSchemaQualifiedName( 'search_result' ),
                            array(
                                (string) $locale,
                                (string) $query,
                                (string) $type,
                                (bool)   $all,
                                (int)    $userId,
                                (int)    $groupId,
                                (int)    $limit  ?: 10,
                                (int)    $offset ?: 0,
                                (bool)   $options->coverDensity,
                                (float)  $options->weightTitle,
                                (float)  $options->weightKeywords,
                                (float)  $options->weightDescription,
                                (float)  $options->weightContent,
                                (int)    $options->normalization,
                                (string) $options->startSelection,
                                (string) $options->stopSelection,
                                (int)    $options->maxWords,
                                (int)    $options->minWords,
                                (int)    $options->shortWord,
                                (int)    $options->maxFragments,
                                (string) $options->fragmentDelimiter,
                            ),
                            FunctionCall::MODE_RESULT_SET
                        );

        $resultSet = new HydratingResultSet(
            $this,
            $this->getStructurePrototype()
        );

        $resultSet->initialize( $result );
        return $resultSet;
    }

    /**
     * Find suggestions
     *
     * @param   mixed   $where
     * @param   int     $limit
     * @return  \Traversable
     */
    public function findSuggestions( $where = null, $limit = null )
    {
        list( $locale, $query, $type, $all ) = $this->parseWhere( $where );

        if ( empty( $query ) )
        {
            return array();
        }

        list( $userId, $groupId ) = $this->getUserIdAndGroupId();

        $result = $this->sql()
                       ->call(
                           $this->getSchemaQualifiedName( 'search_suggestion' ),
                           array(
                               (string) $locale,
                               (string) $query,
                               (string) $type,
                               (bool)   $all,
                               (int)    $userId,
                               (int)    $groupId,
                               (int)    $limit  ?: 10,
                           ),
                           FunctionCall::MODE_RESULT_SET
                       );

        if ( empty( $result ) )
        {
            return array();
        }

        if ( is_array( $result ) )
        {
            $result = new \ArrayIterator( $result );
        }

        return new CallbackMapIterator(
            $result,
            function ( $row ) {
                return $row['search_suggestion'];
            }
        );
    }

    /**
     * Get paginator
     *
     * @param   mixed|null  $where
     * @param   mixed|null  $options
     * @return  \Zend\Paginator\Paginator
     */
    public function getPaginator( $where = null, $options = null )
    {
        return new Paginator(
            new PaginatorAdapter( $this, $where, $options )
        );
    }

}
