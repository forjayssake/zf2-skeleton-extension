<?php
namespace Settings\Form;

use Application\Form\AbstractBaseForm as BaseForm;
use Zend\Form\FormInterface;
use Setting;

class EditSetting extends BaseForm
{
	/**
	 * 
	 * @var string
	 */
	protected $valueElementName;
	
	public function __construct()
	{
		parent::__construct('add_edit_setting');
	}
	
	public function init()
	{
		$this->add([
			'name' => 'value',
			'type' => 'Text',
			'options' => [
				'label' => 'SETTING_VALUE',
			],
		]);
		
		$this->add([
			'name' => 'submit',
			'type' => 'Submit',
			'options' => [
				'label' => 'SAVE_SETTING',
			],
			'attributes' => [
				'class' => 'btn btn-primary',
			],
		]);
		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Application\Form\AbstractBaseForm::bind()
	 */
	public function bind($object, $flags = FormInterface::VALUES_NORMALIZED)
	{
		parent::bind($object);
		return $this;
	}
	
	/**
	 * set the value for the 'value' element from the Ssetting object
	 * @param Setting $setting
	 */
	protected function setSettingElementValue(Setting $setting)
	{
		if (!is_null($setting->getPrimaryKey()))
		{
			$this->get('value')->setValue($setting->getValue());
			$this->get('value')->setOptions(['help-block' => 'This setting must be of type: ' . Setting::$settingTypes[$setting->getType()]]);
		}
		
		return $this;
	}
	
	public function getValueElementName()
	{
		return $this->valueElementName;
	}
}