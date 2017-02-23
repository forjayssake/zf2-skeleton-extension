<?php
namespace Users\Form;

use Application\Form\AbstractBaseForm as BaseForm;
use UserQuery;
use User;

class PasswordForm extends BaseForm
{

	public function __construct()
	{
		parent::__construct('user_password');
	}
	
	public function init()
	{
		$this->add([
			'name' => 'password',
			'type' => 'Password',
			'options' => [
				'label' => 'PASSWORD',
			],
			'attributes' => [
				'autocomplete' => 'off',
				'class' => 'password required ',
			]
		]);
		
		$this->add([
			'name' => 'confirm_password',
			'type' => 'Password',
			'options' => [
				'label' => 'CONFIRM_PASSWORD',
			],
			'attributes' => [
				'autocomplete' => 'off',
				'class' => 'passwordconfirm required ',
			]
		]);
		
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