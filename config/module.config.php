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
            'Grid\Search\Search\DebugQuery' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'     => '/app/:locale/search/debug-query',
                    'defaults'  => array(
                        'controller' => 'Grid\Search\Controller\Search',
                        'action'     => 'debug-query',
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
            'type'      => 'Grid\Search\Form\Search',
            'elements'  => array(
                'query' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Search',
                        'name'      => 'query',
                        'options'   => array(
                            'label'     => 'search.form.query',
                            'required'  => true,
                        ),
                        'attributes'    => array(
                            'data-js-type'  => 'js.search.query',
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
                        'attributes'    => array(
                            'value'         => 10,
                            'data-js-type'  => 'js.search.items',
                        ),
                    ),
                ),
                'submit' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Submit',
                        'name'      => 'submit',
                        'attributes'    => array(
                            'value'         => 'search.form.submit',
                            'data-js-type'  => 'js.search.submit',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\Search\Paragraph' => array(
            'type'      => 'Grid\Search\Form\Search',
            'elements'  => array(
                'query' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Search',
                        'name'      => 'query',
                        'options'   => array(
                            'label'     => false,
                            'required'  => true,
                        ),
                        'attributes'    => array(
                            'data-js-type'  => 'js.search.query',
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
                        'type'      => 'Zork\Form\Element\Hidden',
                        'name'      => 'items',
                        'options'   => array(
                            'label'     => false,
                        ),
                        'attributes'    => array(
                            'value'     => 10,
                        ),
                    ),
                ),
                'submit' => array(
                    'spec' => array(
                        'type'      => 'Zork\Form\Element\Submit',
                        'name'      => 'submit',
                        'attributes'    => array(
                            'value'         => 'search.form.submit',
                            'data-js-type'  => 'js.search.submit',
                        ),
                    ),
                ),
            ),
        ),
        'Grid\Paragraph\CreateWizard\Start' => array(
            'elements'  => array(
                'type'  => array(
                    'spec'  => array(
                        'options'   => array(
                            'options'   => array(
                                'functions'     => array(
                                    'label'     => 'paragraph.type-group.functions',
                                    'order'     => 4,
                                    'options'   => array(
                                        'search' => 'paragraph.type.search',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'Grid\Paragraph\Meta\Edit' => array(
            'fieldsets' => array(
                'search' => array(
                    'spec' => array(
                        'name'      => 'search',
                        'options'   => array(
                            'label'     => 'paragraph.type.search',
                            'required'  => false,
                        ),
                        'elements'  => array(
                            'name'  => array(
                                'spec'  => array(
                                    'type'      => 'Zork\Form\Element\Text',
                                    'name'      => 'name',
                                    'options'   => array(
                                        'label'     => 'paragraph.form.abstract.name',
                                        'required'  => false,
                                    ),
                                ),
                            ),
                            'displayInstallOpensearch'  => array(
                                'spec'  => array(
                                    'type'      => 'Zork\Form\Element\Text',
                                    'name'      => 'displayInstallOpensearch',
                                    'options'   => array(
                                        'label'         => 'search.form.paragraph.displayInstallOpensearch',
                                        'description'   => 'search.form.paragraph.displayInstallOpensearch.description',
                                        'required'      => false,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'modules' => array(
        'Grid\Paragraph' => array(
            'customizeMapForms' => array(
                'search'        => array(
                    'element'   => 'general',
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'mvc_strategies' => array(
            'Grid\Search\Mvc\Strategy\OpenSearchStrategy',
        ),
        'template_map' => array(
            'grid/paragraph/render/search'  => __DIR__ . '/../view/grid/paragraph/render/search.phtml',
            'grid/search/search/index'      => __DIR__ . '/../view/grid/search/search/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
