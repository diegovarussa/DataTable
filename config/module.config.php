<?php

namespace DataTable;

use DataTable\Model\DataTableModel;
use Zend\ServiceManager\ServiceManager;

return [
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __NAMESPACE__ => __DIR__ . '/../public',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'jquery-data-table' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/jquery-data-table',
                    'defaults' => [
                        'controller' => 'DataTable\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            'jquery-data-table-ajax' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/jquery-data-table-ajax',
                    'defaults' => [
                        'controller' => 'DataTable\Controller\Index',
                        'action'     => 'ajax',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'dataTableModel' => function ($sm) {
                /** @var ServiceManager $sm */
                return new DataTableModel($sm);
            },
        ],
    ],
    'controllers' => [
        'invokables' => [
            'DataTable\Controller\Index' => Controller\IndexController::class
        ],
    ],
    'view_manager' => [
//        'display_not_found_reason' => true,
//        'display_exceptions'       => true,
//        'doctype'                  => 'HTML5',
//        'not_found_template'       => 'error/404',
//        'exception_template'       => 'error/index',
//        'template_map' => [
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
//            'error/404'               => __DIR__ . '/../view/error/404.phtml',
//            'error/index'             => __DIR__ . '/../view/error/index.phtml',
//        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];