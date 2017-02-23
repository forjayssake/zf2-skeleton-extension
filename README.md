Zend Framework 2 Extension
==========================

Document Version 1.0

Introduction
------------
Zend Framework 2 extension and skeleton application.  

This documentation assumes basic knowledge of Zend Framework 2 MVC structure, setup and routing configuration.  
Zend Framework reference guide can be found here: http://framework.zend.com/manual/current/en/index.html.  


Bootstrap
---------
CSS framework is provided by Bootstrap 3.0.3. Documentation can be found here:
http://getbootstrap.com/css/  
http://getbootstrap.com/components/  
http://getbootstrap.com/javascript/  

Bootstrap is `responsive` out of the box. While not necessarily `mobile ready` the software should display sensibly on most devices
without too much style modification.


Fontawesome
-----------
Vector icon set. Documentation can be found here:  
https://fortawesome.github.io/Font-Awesome/examples/ (usage)  
https://fortawesome.github.io/Font-Awesome/icons/ (available icons)

This application uses v4.6.3


Requirements & Setup
--------------------
Install PHP ~5.5 - either install manually (http://php.net/manual/en/install.php), or (easier) install via an AMP server, e.g. XAMPP (https://www.apachefriends.org/download.html)

Install Composer - download here: https://getcomposer.org/download/, documentation here: https://getcomposer.org/doc/ 

Pull down this repository via Git, or extract into your local working directory   


Populate Your Vendor Directory
------------------------------
Open the command prompt as an administrator and change directory (cd) to your local working directory.   
Type "composer update" and press enter

The configuration for what Composer installs for the application is stored in \composer.json. Update this file to add new packages.


Initialising Propel ORM
-----------------------
Before the skeleton application can run correctly a database must be set up. The repository contains the generated Propel
classes for the example database, and a framework-base.sql file containing SQL to generate the database and populate it with the minimum details for you
to log into the application.

Simple Setup:  
Create new database in your local MySQL install.  
Amend the connection details in /generated-conf/config.php

Full Propel Setup:  
Propel generates an object relational model when given a database connection and schema. It will build all associated
classes and generate SQL, and create or modify tables in the database as requested. An example schema matching
framework-base.sql is included in this repository.  

The database schema for the application can be found in /data/schema/

Open command prompt  
Navigate to root of local repository  
type: `propel init` (without quotes) and press enter  
Follow prompts

* Class location should be setup as `Db`
* Namespace can be left blank for the current setup 

SQL for the initial database is included in this repository (base-framework.sql), and includes a test role and user.
The SQL to create each table in the schema is also available via propel

Full documentation for Propel can be found here: http://propelorm.org/documentation/

This application currently uses Propel v2.0


User Permissions
-----------------
User permissions are fairly limited out the box, and use Zend's Access Control Lists (ACL). Configuration can be found in
/module/Application/config/application.acl.roles.php.

Permissions are defined by route in a hierarchy (higher roles inherit routes from lower roles). Navigation is displayed 
as per user permissions based on the supplied route.  

Currently ALL routes need to be defined in the application.acl.roles.php file, or errors will occur for unreferenced routes
 - there is possible scope to automate this from the router and/or navigation config.


Supplied Guest Account Details
------------------------------
Guest account username: testuser  
Guest account password: Regedit0!


Navigation Setup
----------------
Menu items to be displayed in the horizontal navigation bar can be configured in module-config.php as follows:

    'navigation' => [
        'default' => [
            'label' => 'USERS',				-- top level navigation
            'route' => 'users',             -- link route
            'icon' => 'fa-user',            -- icon to display against link text
            'base-role' => 'GUEST',			-- this defines the lowest role that can see/access this navigation item - parse as null to display regardless of role
            'sys-admin-only' => true,       -- display to system administrator users only - default false
            'align' => 'left',				-- menu item alignment in the bar, `left` is selected by default

            -- `Tabs` are additionally configured as pages of the top level navigation
            'pages' => [
                [
                    'label' => 'USERS_LIST',                                                -- tab text
                    'route' => 'users',                                                     -- clicking tab directs to this route
                    'routes' => ['users', 'users/add', 'users/view', 'users/view/actions'], -- display the tab on pages matching these routes
                    'icon' => 'fa-list',                                                    -- icon to display against link text
                    'base-role' => 'GUEST',                                                 -- this defines the lowest role that can see/access this navigation item - parse as null to display regardless of role
                ],
            ]
        ],
    ],

The application also supplies an additional `Setup` navigation configuration. Items configured here will appear under the setup drop-down menu

Navigation configurations are hierarchical and will appear in the order specified in the list of included modules

Additional navigation containers can be specified by creating a new factory and referencing this in the module-config.php file as per this example:

    'service_manager' => array(
        'factories' => array(
            'setup_navigation' => 'Application\Navigation\SetupNavigationFactory'
        ),
    ),

The supplied layout will need to be amended (or a new one created) to accommodate the new navigation as required

Logout Menu Options
-------------------
Navigation configuration for logout has additional options:

* show_details - boolean
  set as false (default) to display a single logout option.   
  Set as true to display logout as a dropdown containing user and role information

* show_sys_admin - boolean
  requires show_details be set to true. Displays an additional entry in the user details if logged in user is a system administrator


Global Search Setup
-------------------
Any propel object can be added to the global search by adding a configuration array for it in the 'global_search' config
key in any module-config.php

Example: define objects and fields to include in global search

    'global_search' => [
        'show_search' => true,                      -- display the search form
        'search_input_prompt' => 'Enter Search',    -- placeholder text to display in the search input
         'search_objects' => [
            PHPObjectName' => [                     -- replace with the required PHP propel class name
                'route' => 'route/action',	-- the route to supply to the 'View' option. `id` is currently used as the route parameter by default
                'fields' => ['fieldName1', 'fieldName2', 'fieldName3', ...],
                'icon' => 'fa-iconclass', -- fontawesome class name
                'displayObject' => 'Object Name', -- this is displayed in the header of results for this object
                'displayFields' => ['Field One', 'Field Two', 'Field Three', ...],
            ],
        ]
    ],

Global search results are pre-filtered for user permissions on the 'route' parameter 

Global search can be excluded from the application completely by removing the `global_search` configuration array


Controllers
-----------
New controllers should generally be able to inherit from \Application\Controller\AbstractBaseController.

Extending from this class will pre-load dependencies for the serviceLocator, translator, table generation service and the form
and view helper managers.

A number of helper methods are also available, including an enhanced translation method and form processing.


Example Table Config
--------------------
Examples for setting up a new table can be found in the code comments in:
/module/Applcation/src/Application/Service/PropelTableService

A typical table setup from within a controller action:

	$columns = [
		'id' => [
			'label' => 'ID',			-- header column label, this is a translation string
			'isLink' => true,			-- wrap content in an anchor tag, href links to 'linkRoute' as per $config array below.
												For consistency the first 2 columns should be links if required
			'canSort' => true,			-- column can be sorted
			'filter' => [				-- display optional filter for column
				'type' => 'Text',       -- filter type: currently Text or Select
				'match' => 'exact'      -- match conditions: exact, left, right or both
			],
			'helper' => [                   -- render cell content using a view helper
			    'name' => 'helperName',     -- name of view helper to render column value. Can be a class name as here, or a closure
			    'params' => [[param1, param2] -- additional params - the cell value is parsed to the view helper as the first parameter by default, does not need including here
			 ]
		],
		'firstName' => [
			'label' => 'FIRST_NAME',
			'isLink' => true,
			'canSort' => true,
			'filter' => [
				'type' => 'Text',
				'match' => 'both'
			],
		],
		'lastName' => [
			'label' => 'LAST_NAME',
			'canSort' => true,
			'filter' => [
				'type' => 'Text',
				'match' => 'both'
			],
		],
	];

	$table = $this->propelTableService;
	$config = [
		'columns' => $columns,
		'linkRoute' => 'users/view',
		'linkRouteParams' => ['id' => 'id'],
		'showEditLink' => true,
		'showDeleteLink' => true,
		'sortOnLoad' => '+id',
	];

	$table->setConfig($config)->setPropelModel(UserQuery::create())->prepare();

	return new ViewModel([
		'table' => $table,
	]);


Displaying a table in a view:

    <?php echo $this->partial('partial/table', ['table' => $this->table]); ?>


Forms
-----
A base form class is supplied in \Application\From\AbstractBaseForm providing a template for Propel models.
By default extending from this class will:

* Auto-create a CSRF element
* Auto-identify required fields from the Propel model input filter
* Pre-select single values in select elements

The base class also contains methods for creating elements automatically from the Propel table map

Rendering of forms with bootstrap 3x requires the third party TwbBundle module (included).
Git repository here: https://github.com/neilime/zf2-twb-bundle  
Demo and examples here: http://neilime.github.io/zf2-twb-bundle/demo.html

Where possible form classes should be registered in the FormManager array in the Module.php file in each module.
 To instantiate a form inside a controller action:

    $formManager = $this->serviceLocator->get('FormElementManager');
    $form = $formManager->get('LoginForm');


Creating a Form From a Propel Table Map
---------------------------------------
Form elements can be automatically generated from a Propel table map. All propel objects have an associated map class under the \Map
namespace.

Create a form class that extends \Application\Form\AbstractBaseForm - no elements need to be created in the class.
In your controller the form object can be populated as follows:

$form->generateFromTableMap('MyTableNameMap');

By default the generateFromTableMap function will exclude the `id` and propel auto-timestamp columns, which shouldn't be
user-editable. You can optionally parse an array of column names to exclude from the form (which should include the defaults
if you still want them excluded). Most element properties can be overridden by an optional third `form options`
array parameter.
 
A more complete example:

    $form = new MyForm();
    $formExclusions = ['id', '_user_id', 'status', 'created_at', 'updated_at'];
    $formOptions = [
        'category' => [ // field name to override
            'type' => 'select',
            'empty_option' => 'PLEASE_SELECT',
            'value_options' => [1 => 'value', 2 => 'value', 3 => 'value']       // notice these values do not need to sit in the normal element config structure
        ],
    ];
    $form->generateFromTableMap('RequestTableMap', $formExclusions, $formOptions)->bind($object);

Field Types:

* TIMESTAMP fields are created as datepicker elements
* BOOLEAN fields are created as select elements with default values [0 => 'No', 1 => 'Yes']
* LONGVARCHAR fields are created as textarea elements
* All other field types are created as text type elements unless changed explicitly in the $formOptions parameter


Applying Required Status to Form Fields
---------------------------------------
Required fields are bound automatically by forms extending the \Application\Form\AbstractBaseForm class when a propel object is bound using the $form->bind()
method.

Propel objects requiring this functionality should implement the \Zend\InputFilter\InputFilterAwareInterface interface and return a valid inputFilter config from
its getInputFilter() method.

	$inputFilter->add(array(
		'name' => '_role_id',
		'required' => true,				-- this entry defines a required field
		'validators' => array(
			array(
				'name' => 'Digits',
			),
		)
	));


Form CSRF
---------
Forms extending the \Application\Form\AbstractBaseForm class automatically include a csrf token element with a name
of $form_name + 'csrf' 


Custom Configuration Settings
-----------------------------
Custom settings can be added under the 'app_settings' key in any module-config.php file


Translations
------------
Each module should contain a `lang` directory containing a translation file (`en_GB.php` by default). This file contains 
an array of translation constants and their associated translation values.

All display text in the application should go through the translation view helper. 

Example, from within a view:

    <?php echo $this->translate('TRANSLATION_CONSTANT'); ?>

sprintf can be used to parse parameters to translation strings. Example call:

    <?php echo sprintf($this->translate('TRANSLATION_CONSTANT_X'), $this->user->getfullName()); ?>

Associated sprintf translation string in en_GB.php:

    'TRANSLATION_CONSTANT_X' => 'Viewing details for: %s',

Controllers extending the AbstractBaseController class have access to an extended translate() method and can bypass sprintf,
parsing parameters directly to translate:

    $this->translate('TRANSLATION_CONSTANT_X', [$user->getFullName()]);


User Role Constants
-------------------
User role constants from the Roles table should be stored in the user_roles_constants.php file in the root of the repository. The `Guest` role is defined here 
as an example.

These definitions allow the constants to the called anywhere in the code.

An additonal SETUP_ROLES constant is supplied, accepting an array of role constants with access to view the setup menu.
This constant uses the json_encode method to allow for applications using PHP < 5.6. PHP 5.6+ allows the creation of arrays
as constants. The assertion used to validate rendering the setup menu will accept either a json_encoded string, or an array, so if
using newer versions of PHP this constant can be rewritten to use a standard array.


Bespoke User Data
-----------------

User objects contain a mechanism to persist and retrieve any arbitrary associated values, managed by the following methods:  
* getUserDataObject()
* setUserDataJSON($object)
* setDataValue($key, $value)
* getDataValue($key)  
  
Typically only setDataValue() and getDataValue() need to be called, the other methods being used store and return the user data object internally. 


Supplied View Helpers
---------------------

* ConfirmModal 		- Simple modal with OK/Cancel
* GenericModal 		- Base modal helper with a number of configuration options
* RenderTickCrossNull - Render a tick/cross badge for a boolean or equivalent value
* ShowFlashMessages 	- Application view helper to display flash messages in the layout
* StringTruncate 		- Display a string to _n_ characters
* RenderEmail         - Format an email address with optional mailto link, icon and subject
* RenderLargeText     - Wrap lengthy text in a vertically scrollable div
* RenderPercentageBar - Display a percentage bar


Supplied Modules
----------------

* AccessManagement - Administration level control of access to the application for each defined user role
* Application - Core application functionality, Base forms and controllers, services supporting all modules
* Db - Populated by Propel ORM
* Email - Basic templating and email scheduling functionality - in development
* Settings - User editable internal application settings 
* Users - User management


Exceptions
----------

The included Application\Exception\GenericException class will automatically add the calling class and method name
to exception messages when used.

GenericException is included in the imports for the Application\Controller\AbstractBaseController class.


Other Fun Stuff:
----------------
The base application also comes with:

* Leaflet maps and the Mapper.js helper object to make creating simple maps easy
* CKEditor HTML/WYSIWYG editor can be used to convert any textarea element into an HTML/WYSIWYG editor
* Tooltip elements can be defined by adding a 'tooltip-element' class - see bootstrap javascript documentation for more information
* QR Code generator