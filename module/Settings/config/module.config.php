<?php
namespace Settings;

return [
	
	'navigation' => [
		'setup' => [
			[
				'label' => 'SETTINGS',
				'route' => 'settings',
				'icon' => 'fa-wrench',
				'base-role' => 'CAMPUS_SERVICES_ADMINISTRATOR',
				'pages' => [
					[
						'label' => 'SETTINGS_LIST',
						'route' => 'settings',
						'routes' => ['settings', 'settings/view/actions'],
						'icon' => 'fa-list',
						'base-role' => 'CAMPUS_SERVICES_ADMINISTRATOR',
					],
				],
			],
		],
	],
	
	'router' => [
		'routes' => [
			'settings' => [
				'type' => 'segment',
				'options' => [
					'route'    => '/settings',
					'defaults' => [
						'controller' => 'Settings\Controller\SettingsController',
						'action'     => 'index',
					],
				],
				'may_terminate' => true,
				'child_routes' => [
					'view' => [
						'type' => 'segment',
						'options' => [
							'route' => '/:id',
							'constraints' => [
								'id' => '[0-9]+',
							],
							'defaults' => [
								'action' => 'view',
							],
						],
						'may_terminate' => true,
						'child_routes' => [
							'actions' => [
								'type' => 'segment',
								'options' => [
									'route'    => '/:action',
									'constraints' => [
									],
									'defaults' => [
										'controller' => 'Settings\Controller\SettingsController',
										'action'     => 'view',
									],
								],
							],
						],
					],
				],
			],
		],
	],
		
	'translator' => [
		'locale' => 'en_GB',
		'fallbackLocale' => 'en_GB',
		'translation_file_patterns' => [
			[
				'type'     => 'phparray',
				'base_dir' => __DIR__ . '/../lang',
				'pattern'  => '%s.php',
			],
		],
	],
	
	'service_manager' => [
		'factories' => [
			'SettingService' => 'Settings\Factory\SettingServiceFactory',
		],
	],
		
	'controllers' => [
		'invokables' => [
			'Settings\Controller\SettingsController' => Controller\SettingsController::class,
		],
		'delegators' => [
			'Settings\Controller\SettingsController' => [
				'Application\Delegator\AbstractBaseControllerDelegatorFactory'
			],
		],
	],
	
	'view_helpers' => [
		'invokables' => [
		],
	],
	
	'view_manager' => [
		'template_map' => [
			//'partial/plant-locations'    => __DIR__ . '/../view/plants/partial/plant-locations.phtml',
		],
		'template_path_stack' => [
			__DIR__ . '/../view',
		],
	],
	
	// Placeholder for console routes
	'console' => [
		'router' => [
			'routes' => [
			],
		],
	],
	
];