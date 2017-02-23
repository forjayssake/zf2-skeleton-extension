<?php
namespace Email;

return array(


	/**
	 * define events and parameters for email templates here
	 * 'event' => 'parameters'
	 */
	'template_options' => [
		'event.testExample' => [
			'params' => [
				'fullName' => [
					'object' => 'User',
					'display' => 'Full Name',
				],
				'email' => [
					'object' => 'User',
					'display' => 'Email',
				],
				'username' => [
					'object' => 'User',
					'display' => 'Username',
				],
			],
		],
	],

	'navigation' => [
		'setup' => [
			[
				'label' => 'TEMPLATES',
				'route' => 'templates',
				'icon' => 'fa-newspaper-o',
				'base-role' => 'GUEST',
				'pages' => [
					[
						'label' => 'TEMPLATES_LIST',
						'route' => 'templates',
						'routes' => ['templates', 'templates/add', 'templates/view', 'templates/view/actions'],
						'icon' => 'fa-list',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'TEMPLATES_ADD',
						'route' => 'templates/add',
						'routes' => ['templates', 'templates/add'],
						'icon' => 'fa-plus',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'TEMPLATES_VIEW',
						'route' => 'templates/view',
						'routes' => ['templates/view', 'templates/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-share',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'TEMPLATES_EDIT',
						'route' => 'templates/view/actions',
						'action' => 'edit',
						'routes' => ['templates/view', 'templates/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-edit',
						'base-role' => 'GUEST',
					],
					[
						'label' => 'TEMPLATES_DELETE',
						'route' => 'templates/view/actions',
						'action' => 'delete',
						'routes' => ['templates/view', 'templates/view/actions'],
						'params' => ['id' => 0],
						'icon' => 'fa-remove',
						'base-role' => 'GUEST',
						'sys-admin-only' => true,
					],
				],
			],
		]
	],

	'router' => array(
		'routes' => array(
			'emails' => array(
				'type'    => 'literal',
				'options' => array(
					'route'    => '/emails',
					'defaults' => array(
						'controller' => 'Email\Controller\EmailController',
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
										'controller' => 'Email\Controller\EmailController',
										'action'     => 'view',
									),
								),
							),
						),
					),
				),
			),
			'templates' => array(
				'type'    => 'literal',
				'options' => array(
					'route'    => '/templates',
					'defaults' => array(
						'controller' => 'Email\Controller\TemplatesController',
						'action'     => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'template-parameters' => array(
						'type' => 'segment',
						'priority' => 1000,
						'options' => array(
							'route'    => '/template-parameters',
							'defaults' => array(
								'action'     => 'fetchEventParameters',
							),
						),
						'may_terminate' => true,
					),
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
										'controller' => 'Email\Controller\TemplatesController',
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
			'Email\Controller\EmailController' => Controller\EmailController::class,
			'Email\Controller\TemplatesController' => Controller\TemplatesController::class,
		],
		'delegators' => [
			'Email\Controller\TemplatesController' => [
				'Application\Delegator\AbstractBaseControllerDelegatorFactory'
			],
		]
	],

	'view_helpers' => [
		'invokables' => [
		],
		'factories' => [
			'RenderTemplateParameters' => 'Email\Factory\RenderTemplateParametersFactory',
		],
	],

	'service_manager' => array(
		'factories' => array(
			'AddEditTemplateForm' => 'Email\Factory\AddEditTemplateFactory',
		),
	),

	'view_manager' => array(
		'template_map' => array(
			'partial/event-parameters-form' => __DIR__ . '/../view/email/partial/event-parameters-form.phtml',
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
