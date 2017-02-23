<?php
namespace Email\Form;

use Application\Form\AbstractBaseForm as BaseForm;

class AddEditTemplate extends BaseForm
{

	/**
	 * @var array
	 */
	protected $eventOptions = [];

	public function __construct(array $eventOptions = [])
	{
		parent::__construct('add_edit_template');
		$this->eventOptions = $eventOptions;
	}
	
	public function init()
	{
		$this->add([
			'name' => 'name',
			'type' => 'Text',
			'options' => [
				'label' => 'NAME',
			],
			'attributes' => [
				'class' => 'templates-element',
			],
		]);

		$this->add([
			'name' => 'event',
			'type' => 'Select',
			'options' => [
				'label' => 'EVENT',
				'empty_option' => 'PLEASE_SELECT_SHORT',
				'value_options' => $this->eventOptions,
			],
			'attributes' => [
				'class' => 'templates-element',
				'id' => 'template-events',
			],
		]);

		$this->add([
			'name' => 'subject',
			'type' => 'Text',
			'options' => [
				'label' => 'SUBJECT',
			],
			'attributes' => [
				'class' => 'templates-element',
			],
		]);

		$this->add([
			'name' => 'body',
			'type' => 'Textarea',
			'options' => [
				'label' => 'BODY',
			],
			'attributes' => [
				'class' => 'templates-element templates-ckeditor',
				'id' => 'bodyHtml'
			],
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