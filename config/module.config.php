<?php

namespace DataTable;

use DataTable\Model\DataTableModel;
use Zend\ServiceManager\ServiceManager;

return [
    'service_manager' => [
        'factories' => [
            'dataTableModel' => function ($sm) {
                /** @var ServiceManager $sm */
                return new DataTableModel($sm);
            },
        ],
    ],
];