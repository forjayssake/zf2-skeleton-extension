<?php
namespace Users;

return array(

	'navigation' => [
		'setup' => [
			[
				'label' => 'USERS',
				'route' => 'users',
				'icon' => 'fa-user',
				'base-role' => 'GUEST',
				'pages' => [
					[
						'label' => 'USERS_LIST',
						'route' => 'users',
						'routes' => ['users', 'users/add', 'users/view', 'users/view/actions'],
						'icon' => 'fa-list',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'USERS_ADD',
						'route' => 'users/add',
						'routes' => ['users', 'users/add'],
						'icon' => 'fa-plus',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'USERS_VIEW',
						'route' => 'users/view',
						'routes' => ['users/view', 'users/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-share',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'USERS_EDIT',
						'route' => 'users/view/actions',
						'action' => 'edit',
						'routes' => ['users/view', 'users/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-edit',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'USERS_DELETE',
						'route' => 'users/view/actions',
						'action' => 'delete',
						'routes' => ['users/view', 'users/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-remove',
						'base-role' => 'GUEST',
					],
				],
			],
		]
	],

	'router' => array(
		'routes' => array(
			'users' => array(
				'type'    => 'literal',
				'options' => array(
					'route'    => '/users',
					'defaults' => array(
						'controller' => 'Users\Controller\UsersController',
						'action'     => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'add' => array(
						'type' => 'segment',
						'priority' => 1000,
						'options' => array(
							'route'    => '/add',
							'defaults' => array(
								'action'     => 'add',
							),
						),
						'may_terminate' => true,
					),
					'view' => array(
						'type' => 'segment',
						'options' => array(
							'route'    => '/:id',
							'constraints' => array(
								'id'     => '[0-9]+',
							),
							'defaults' => array(
								'action'     => 'view',
							),
						),
						'may_terminate' => true,
						'child_routes' => array(
							'actions' => array(
								'type' => 'segment',
								'options' => array(
									'route'    => '/:action',
									'constraints' => array(
									),
									'defaults' => array(
										'controller' => 'Users\Controller\UsersController',
										'action'     => 'view',
									),
								),
							),
						),
					),
				),
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
			'Users\Controller\UsersController' => Controller\UsersController::class,
		],
		'delegators' => [
			'Users\Controller\UsersController' => [
				'Application\Delegator\AbstractBaseControllerDelegatorFactory'
			],
		]
	],

	'view_helpers' => [
		'invokables' => [

		],
	],

	'service_manager' => array(
		'factories' => array(
			'AddEditUserForm' => 'Users\Factory\AddEditUserFactory',
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
