<?php
namespace Application;

return array(

	'app_settings' => [
		'default_table_date_format' => 'M j Y', 
	],

	'navigation' => [
		'default' => [
			[
				'label' => 'LOGOUT',
				'route' => 'logout',
				'icon' => 'fa-power-off',
				'align' => 'right',			// defaults to left
				'show_details' => true,
				'show_sys_admin' => true,
			],
		],
		'setup' => [
			
		],
	],
	
	// site pages not requiring authentication
	'authentication_exempt' => [
		'login',
		'logout',
	],
	
	/**
	 * define objects and fields to include in global search
	 * example:
	 * 'search_objects' => [
	 		PHPObjectName' => [ 
				'route' => 'route/action',	-- id is currently used as the route parameter by default
				'fields' => ['fieldName1', 'fieldName2', 'fieldName3', ...],
				'icon' => 'fa-iconclass', -- fontawesome class name
				'displayObject' => 'ObjectName',
				'displayFields' => ['Field One', 'Field Two', 'Field Three', ...],
			],
		]
	 */
	'global_search' => [
		'show_search' => true,
		'search_namespace' => null,
		'search_input_prompt' => 'Enter Search',
		'search_objects' => [
			'User' => [
				'route' => 'users/view',
				'fields' => ['title', 'firstName', 'lastName', 'email', 'username'],
				'displayFields' => ['Title', 'Forename', 'Surname', 'Email Address', 'Username'],
				'displayObject' => 'User',
				'icon' => 'fa fa-user',
			],
		],
	],
	
	'authentication' => [
		'authentication_methods' => [\User::AUTHENTICATE_LDAP, \User::AUTHENTICATE_DB],
		'auto_create_account' => [\User::AUTHENTICATE_LDAP],
		'enable_sso' => false,
		'auto_create_account_mapping' => [
			\User::AUTHENTICATE_LDAP => [
				'User' => [
					'title' => 'title',
					'firstName' => 'edupersonnickname',
					'lastName' => 'sn',
					'email' => 'mail',
					'username' => 'uid',
				]
			]
		],
		'invalid_login_message' => 'LOGIN_FAILED_GENERIC',
		'sync_ldap_details_on_login' => false,
	],
	
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\IndexController',
                        'action'     => 'index',
                    ),
                ),
            ),
        	'global-search' => array(
        		'type' => 'Zend\Mvc\Router\Http\Literal',
        		'options' => array(
        			'route'    => '/global-search',
        			'defaults' => array(
        				'controller' => 'Application\Controller\IndexController',
        				'action'     => 'globalSearch',
        			),
        		),
        	),
        	'logout' => array(
        		'type' => 'Zend\Mvc\Router\Http\Literal',
        		'options' => array(
        			'route'    => '/logout',
        			'defaults' => array(
        				'controller' => 'Application\Controller\IndexController',
        				'action'     => 'logout',
        			),
        		),
        	),
        	'login' => array(
        		'type' => 'Zend\Mvc\Router\Http\Literal',
        		'options' => array(
        			'route' => '/login',
        			'defaults' => array(
        				'controller' => 'Application\Controller\IndexController',
        				'action'     => 'login',
        			),
        		),
        	),

            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access-management-management them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        	'Zend\Navigation\Service\NavigationAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        	'PropelTable' => 'Application\Factory\PropelTableServiceFactory',
        	'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        	'setup_navigation' => 'Application\Navigation\SetupNavigationFactory',
        ),
		'involkables' => array(

		),
    ),
    'controller_plugins' => array (
        'invokables' => array (
            'tableExportWidget' => 'Application\Controller\Plugin\ExportTableControllerPlugin'
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
            'Application\Controller\IndexController' => Controller\IndexController::class,
        ],
    	'delegators' => [
    		'Application\Controller\IndexController' => [
    			'Application\Delegator\AbstractBaseControllerDelegatorFactory'
    		],
    	]
    ],
	
	'view_helpers' => [
		'invokables' => [
			'ShowFlashMessages' => 'Application\View\Helper\ShowFlashMessages',
			'RenderTickCrossNull' => 'Application\View\Helper\RenderTickCrossNull',
			'StringTruncate' => 'Application\View\Helper\StringTruncate',
			'GenericModal' => 'Application\View\Helper\GenericModal',
			'ConfirmModal' => 'Application\View\Helper\ConfirmModal',
			'RenderLargeText' => 'Application\View\Helper\RenderLargeText',
			'RenderEmail' => 'Application\View\Helper\RenderEmail',
		],
	],
	
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        	'layout/login'           => __DIR__ . '/../view/layout/login.phtml',
        	'layout/error'            => __DIR__ . '/../view/layout/error.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
        	'error/403'               => __DIR__ . '/../view/error/403.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        	
        	'partial/navigation/uoe-navigation' => __DIR__ . '/../view/partial/navigation/uoe-navigation.phtml',
        	'partial/navigation/uoe-navigation-tabs' => __DIR__ . '/../view/partial/navigation/uoe-navigation-tabs.phtml',
        	'partial/navigation/uoe-navigation-logout' => __DIR__ . '/../view/partial/navigation/uoe-navigation-logout.phtml',
        	'partial/navigation/uoe-navigation-setup'  => __DIR__ . '/../view/partial/navigation/uoe-navigation-setup.phtml',
        	
        	'partial/base/header'    => __DIR__ . '/../view/partial/base/header.phtml',
        	'partial/base/footer'    => __DIR__ . '/../view/partial/base/footer.phtml',

        	'partial/table'			=> __DIR__ . '/../view/partial/table/table.phtml',
        	'partial/table-header'	=> __DIR__ . '/../view/partial/table/table-header.phtml',
        	'partial/table-pagination'	=> __DIR__ . '/../view/partial/table/table-pagination.phtml',
        	'partial/table-checkbox-form'	=> __DIR__ . '/../view/partial/table/table-checkbox-form.phtml',
        	'partial/table-form-partial' 	=> __DIR__ . '/../view/partial/table/table-form-partial.phtml',
        	
        	'partial/modal'			=> __DIR__ . '/../view/partial/modal.phtml',
        	'partial/global-search-results' => __DIR__ . '/../view/partial/global-search-results.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    	'strategies' => array(
    		'ViewJsonStrategy',
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
