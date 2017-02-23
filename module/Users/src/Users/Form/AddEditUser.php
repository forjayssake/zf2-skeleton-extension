<?php
namespace Users\Form;

use Application\Form\AbstractBaseForm as BaseForm;
use UserQuery;
use User;
use Role;

class AddEditUser extends BaseForm
{

	/**
	 * @var array
	 */
	protected $authenticationOptions;

	public function __construct(array $authenticationOptions)
	{
		$this->authenticationOptions = $authenticationOptions;
		parent::__construct('add_edit_user');
	}
	
	public function init()
	{
		$this->add([
			'name' => '_role_id',
			'type' => 'Select',
			'options' => [
				'label' => 'ROLE',
				'empty_option' => 'PLEASE_SELECT_SHORT',
				'value_options' => Role::getRoleSelectOptions(),
			],
			'attributes' => [
				'class' => 'userform-roleid',
			],
		]);
		
		$this->add([
			'name' => 'title',
			'type' => 'Select',
			'options' => [
				'label' => 'TITLE',
				'empty_option' => 'PLEASE_SELECT_SHORT',
				'value_options' => User::getTitleSelectOptions(),
			],
			'attributes' => [
				'class' => 'userform-title',
			],
		]);
		
		$this->add([
			'name' => 'firstName',
			'type' => 'Text',
			'options' => [
				'label' => 'FIRST_NAME',
			],
			'attributes' => [
				'class' => 'userform-firstname',
			],
		]);
		
		$this->add([
			'name' => 'lastName',
			'type' => 'Text',
			'options' => [
				'label' => 'LAST_NAME',
			],
			'attributes' => [
				'class' => 'userform-lastname',
			],
		]);
		
		$this->add([
			'name' => 'email',
			'type' => 'Text',
			'options' => [
				'label' => 'EMAIL',
			],
			'attributes' => [
				'class' => 'userform-email',
			],
		]);
		
		$this->add([
			'name' => 'username',
			'type' => 'Text',
			'options' => [
				'label' => 'USERNAME',
			],
			'attributes' => [
				'class' => 'userform-username',
			],
		]);	
		
		$this->add([
			'name' => 'authenticationType',
			'type' => 'Select',
			'options' => [
				'label' => 'AUTHENTICATION_TYPE',
				'empty_option' => 'PLEASE_SELECT_SHORT',
				'value_options' => $this->authenticationOptions,
			],
			'attributes' => [
				'class' => 'userform-authenticationtype',
			],
		]);
		
		if (User::getLoggedIn()->getSystemAdministrator())
		{
			$this->add([
				'name' => 'systemAdministrator',
				'type' => 'Checkbox',
				'options' => [
					'label' => 'SYSTEM_ADMINISTRATOR',
				],
				'attributes' => [
					'class' => 'userform-systemadministrator',
				],
			]);
		}
		
		$this->add([
			'name' => 'save',
			'type' => 'Submit',
			'attributes' => [
				'value' => 'SAVE_DETAILS',
				'class' => 'btn btn-primary'
			]
		]);
		
		
	}


}