<?php
namespace AccessManagement\Form;

use Zend\InputFilter\InputFilterProviderInterface;
//use Application\Hydrator\Strategy\DateTimeStrategy;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy AS DateTimeStrategy;
use AccessManagementLog AS AML;
use Zend\Form\Form;
use RoleQuery;

class AccessManagementForm extends Form implements InputFilterProviderInterface
{
    const ALERT_TYPE_INFO       = 'alert-info';
    const ALERT_TYPE_WARNING    = 'alert-warning';
    const ALERT_TYPE_ERROR      = 'alert-danger';

    /**
     * @var array
     */
    public static $alertTypes = [
        self::ALERT_TYPE_INFO       => 'ALERT_TYPE_INFO',
        self::ALERT_TYPE_WARNING    => 'ALERT_TYPE_WARNING',
        self::ALERT_TYPE_ERROR      => 'ALERT_TYPE_ERROR',
    ];

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @var array
     */
    protected $logElements = [];

    /**
     * @var string
     */
    protected $name = 'access_management_options';

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i';

    public function __construct()
    {
        parent::__construct($this->name);

        $this->logElements = [
            'allroles' . AML::DISABLED_LOGIN_ELEMENT_SUFFIX,
            'allroles' . AML::MESSAGE_ELEMENT_SUFFIX,
            'allroles' . AML::MESSAGE_TYPE_ELEMENT_SUFFIX,
        ];

        $this->roles = RoleQuery::create()->find();

        $this->add([
            'name' => $this->name . 'csrf',
            'type' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 1800,
                ],
            ],
        ]);

        $this->add([
            'name' => 'allroles' . AML::DISABLED_LOGIN_ELEMENT_SUFFIX,
            'type' => 'checkbox',
            'options' => [
                'checked_value' => AML::ACCESS_DENIED,
                'unchecked_value' => 0,
                'label' => 'DISABLE_LOGIN_ALL',
            ],
            'attributes' => [
                'class' => 'header-element',
                'data-section-title' => 'Access Rules for All Users',
            ],
        ]);

        $this->add([
            'name' => 'allroles' . AML::MESSAGE_ELEMENT_SUFFIX,
            'type' => 'Textarea',
            'options' => [
                'label' => 'ALL_ROLES_MESSAGE',
                'help-block' => 'MESSAGE_APPEARS_LOGIN_PAGE_PRE_LOGIN',
            ],
            'attributes' => [
                'class' => 'access-message access-ckeditor',
                'data-name' => 'All Users Message',
                'id' => 'all_roles_editor',
            ],
        ]);

        $this->add([
            'name' => 'allroles' . AML::MESSAGE_TYPE_ELEMENT_SUFFIX,
            'type' => 'Select',
            'options' => [
                'label' => 'ALL_ROLES_MESSAGE_TYPE',
                'empty_option' => null,
                'value_options' => self::$alertTypes,
            ],
            'attributes' => [
                'class' => 'access-message-type',
            ],
        ]);

        $this->add([
            'name' => 'allroles' . AML::DATETIME_FROM_ELEMENT_SUFFIX,
            'type' => 'date',
            'options' => [
                'label' => 'ALL_ROLES_DATETIME_FROM',
                'add-on-append' => '<i class="fa fa-calendar datepicker-append"></i>',
            ],
            'attributes' => [
                'class' => 'datepicker',
            ],
        ]);

        $this->add([
            'name' => 'allroles' . AML::DATETIME_TO_ELEMENT_SUFFIX,
            'type' => 'date',
            'options' => [
                'label' => 'ALL_ROLES_DATETIME_TO',
                'add-on-append' => '<i class="fa fa-calendar datepicker-append"></i>',
            ],
            'attributes' => [
                'class' => 'datepicker',
            ],
        ]);

        foreach($this->roles as $role)
        {
            $roleName = strtolower($role->getconstant());

            $this->logElements[] = $roleName . \AccessManagementLog::DISABLED_LOGIN_ELEMENT_SUFFIX;
            $this->add([
                'name' => $roleName . \AccessManagementLog::DISABLED_LOGIN_ELEMENT_SUFFIX,
                'type' => 'checkbox',
                'options' => [
                    'checked_value' => AML::ACCESS_DENIED,
                    'unchecked_value' => 0,
                    'label' => 'DISABLE_LOGIN_' . $role->getconstant(),
                ],
                'attributes' => [
                    'class' => 'header-element',
                    'data-section-title' => 'Access Rules for ' . $role->getconstant() . ' Users',
                ],
            ]);

            $this->logElements[] = $roleName . AML::MESSAGE_ELEMENT_SUFFIX;
            $this->add([
                'name' => $roleName . AML::MESSAGE_ELEMENT_SUFFIX,
                'type' => 'Textarea',
                'options' => [
                    'label' => 'ROLES_MESSAGE_' . $role->getconstant(),
                    'help-block' => 'MESSAGE_APPEARS_LOGIN_PAGE_POST_LOGIN',
                ],
                'attributes' => [
                    'class' => 'access-message access-ckeditor',
                    'data-name' => ucfirst($roleName) . ' Message',
                    'id' =>  $roleName . '_editor',
                ],
            ]);

            $this->logElements[] = $roleName . AML::MESSAGE_TYPE_ELEMENT_SUFFIX;
            $this->add([
                'name' => $roleName . AML::MESSAGE_TYPE_ELEMENT_SUFFIX,
                'type' => 'Select',
                'options' => [
                    'label' => 'ROLES_MESSAGE_TYPE_' . $role->getconstant(),
                    'empty_option' => null,
                    'value_options' => self::$alertTypes,
                ],
                'attributes' => [
                    'class' => 'access-message-type',
                ],
            ]);

            $this->logElements[] = $roleName . AML::DATETIME_FROM_ELEMENT_SUFFIX;
            $this->add([
                'name' => $roleName . AML::DATETIME_FROM_ELEMENT_SUFFIX,
                'type' => 'date',
                'options' => [
                    'label' => 'ROLES_DATETIME_FROM_' . $role->getconstant(),
                    'add-on-append' => '<i class="fa fa-calendar datepicker-append"></i>',
                ],
                'attributes' => [
                    'class' => 'datepicker',
                ],
            ]);

            $this->logElements[] = $roleName . AML::DATETIME_TO_ELEMENT_SUFFIX;
            $this->add([
                'name' => $roleName . AML::DATETIME_TO_ELEMENT_SUFFIX,
                'type' => 'date',
                'options' => [
                    'label' => 'ROLES_DATETIME_TO_' . $role->getconstant(),
                    'add-on-append' => '<i class="fa fa-calendar datepicker-append"></i>',
                ],
                'attributes' => [
                    'class' => 'datepicker',
                ],
            ]);

        }

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'options' => [
                'label' => 'SAVE_CONFIGURATION',
            ],
            'attributes' => [
                'class' => 'btn btn-primary',
            ],
        ]);

        $this->add([
            'name' => 'reset',
            'type' => 'Button',
            'options' => [
                'label' => 'CLEAR_CONFIG_AND_SAVE',
            ],
            'attributes' => [
                'class' => 'btn btn-danger',
                'id' => 'reset_access_config'
            ],
        ]);

        $this->configureForAccessConfig();
    }

    /**
     * set form element values from latest access config
     * @return void
     */
    protected function configureForAccessConfig()
    {
        $config = AML::fetchCurrentConfig();

        if (!is_null($config))
        {
            foreach($this->fetchLogElements() as $key => $elementName)
            {
                if (isset($config->$elementName))
                {
                    $this->get($elementName)->setValue($config->$elementName);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $inputFilter = [
            'allroles' . AML::DATETIME_FROM_ELEMENT_SUFFIX => ['required' => false],
            'allroles' . AML::DATETIME_TO_ELEMENT_SUFFIX => ['required' => false]
        ];

        foreach($this->roles as $role)
        {
            $roleName = strtolower($role->getconstant());
            $inputFilter[ $roleName . AML::DATETIME_FROM_ELEMENT_SUFFIX] = ['required' => false];
            $inputFilter[ $roleName . AML::DATETIME_TO_ELEMENT_SUFFIX] = ['required' => false];
        }

        return $inputFilter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|\Propel\Runtime\Collection\ObjectCollection|\Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function fetchLogElements()
    {
        return $this->logElements;
    }
}