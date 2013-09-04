<?php

return array(
    'router' => array(
        'routes' => array(
            'Grid\Search\Search\Index' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\Search',
                        'action'     => 'index',
                    ),
                ),
            ),
            'Grid\Search\Search\Autocomplete' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/autocomplete.json',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\Search',
                        'action'     => 'autocomplete',
                    ),
                ),
            ),
            'Grid\Search\OpenSearch\Atom' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/opensearch/atom.xml',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\OpenSearch',
                        'action'     => 'atom',
                    ),
                ),
            ),
            'Grid\Search\OpenSearch\Rss' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/opensearch/rss.xml',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\OpenSearch',
                        'action'     => 'rss',
                    ),
                ),
            ),
            'Grid\Search\OpenSearch\Description' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/opensearch/description.xml',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\OpenSearch',
                        'action'     => 'description',
                    ),
                ),
            ),
            'Grid\Search\OpenSearch\SuggestionsJson' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/opensearch/suggestions.json',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\OpenSearch',
                        'action'     => 'suggestions-json',
                    ),
                ),
            ),
            'Grid\Search\OpenSearch\SuggestionsXml' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/opensearch/suggestions.xml',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\OpenSearch',
                        'action'     => 'suggestions-xml',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Grid\Search\Controller\Search'     => 'Grid\Search\Controller\SearchController',
            'Grid\Search\Controller\OpenSearch' => 'Grid\Search\Controller\OpenSearchController',
        ),
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            'search' => array(
                'type'          => 'phpArray',
                'base_dir'      => __DIR__ . '/../languages/search',
                'pattern'       => '%s.php',
                'text_domain'   => 'search',
            ),
        ),
    ),
    'factory' => array(
        'Grid\Paragraph\Model\Paragraph\StructureFactory' => array(
            'adapter' => array(
                'search' => 'Grid\Search\Model\Paragraph\Structure\Search',
            ),
        ),
    ),
    'form' => array(
        'Grid\Search\Search' => array(
            'elements'  => array(
                'query' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Search',
                        'name'      => 'query',
                        'options'   => array(
                            'label'     => 'search.form.query',
                            'required'  => true,
                        ),
                    ),
                ),
                'type' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Hidden',
                        'name'      => 'type',
                        'options'   => array(
                            'label'     => false,
                        ),
                        'attributes'    => array(
                            'value'     => '%',
                        ),
                    ),
                ),
                'all' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Checkbox',
                        'name'      => 'all',
                        'options'   => array(
                            'label'         => false,
                            'required'      => false,
                            'label_enable'  => 'search.form.all',
                        ),
                    ),
                ),
                'items' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Select',
                        'name'      => 'items',
                        'options'   => array(
                            'label'     => 'search.form.items',
                            'required'  => false,
                            'options'   => array(
                                5   => array( 'label' => 5,  'value' => 5  ),
                                10  => array( 'label' => 10, 'value' => 10 ),
                                15  => array( 'label' => 15, 'value' => 15 ),
                                20  => array( 'label' => 20, 'value' => 20 ),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Submit',
                        'name'      => 'submit',
                        'attributes'    => array(
                            'value'     => 'search.form.submit',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'grid/paragraph/render/search'  => __DIR__ . '/../view/grid/paragraph/render/search.phtml',
            'grid/search/search/index'      => __DIR__ . '/../view/grid/search/search/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
