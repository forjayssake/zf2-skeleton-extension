<?php
namespace AccessManagement;

return array(

    'navigation' => [
        'setup' => [
            [
                'label' => 'ACCESS_MANAGEMENT',
                'route' => 'access',
                'icon' => 'fa-lock',
                'base-role' => 'GUEST',
            ],
        ]
    ],

    'router' => array(
        'routes' => array(
            'access' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/access',
                    'defaults' => array(
                        'controller' => 'AccessManagement\Controller\AccessManagementController',
                        'action'     => 'edit',
                    ),
                ),
                'may_terminate' => true,
            ),
        )
    ),

    'translator' => array(
        'locale' => 'en_GB',
        'fallbackLocale' => 'en_GB',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../lang',
                'pattern'  => '%s.php',
            ),
        ),
    ),

    'controllers' => [
        'invokables' => [
            'AccessManagement\Controller\AccessManagementController' => Controller\AccessManagementController::class,
        ],
        'delegators' => [
            'AccessManagement\Controller\AccessManagementController' => [
                'Application\Delegator\AbstractBaseControllerDelegatorFactory'
            ],
        ]
    ],

    'view_helpers' => [
        'invokables' => [
            'RenderAccessManagementMessages' => 'AccessManagement\View\Helper\RenderAccessManagementMessages',
        ],
    ],

    'service_manager' => array(
        'factories' => array(

        ),
    ),

    'view_manager' => array(

        'template_map' => array(
            //'partial/example-partial'    => __DIR__ . '/../view/users/partial/example-partial.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
