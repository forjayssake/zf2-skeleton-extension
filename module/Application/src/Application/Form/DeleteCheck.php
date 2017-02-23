<?php
namespace Application\Form;

use Zend\Form\Form;

class DeleteCheck extends Form
{
	
	public function __construct()
	{
		parent::__construct('delete_check');
		
		$this->add(array(
				'name' => 'delete_check_csrf',
				'type' => 'csrf',
		));
		
		$this->add(array(
			'name' => 'confirm',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'CONFIRM_DELETE',
				'class' => 'btn-danger',
			),
		));
		
		$this->add(array(
			'name' => 'cancel',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'CANCEL',
			),
		));
		
	}
		
		
	public function canDelete($data)
	{
		$this->setData($data);
		if($this->isValid())
		{
			$data = $this->getData();
				
			if($data['cancel'] === null)
				return true;
		}
		return false;
	}
	
}