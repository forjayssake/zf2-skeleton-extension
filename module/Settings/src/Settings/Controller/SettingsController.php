<?php
namespace Settings\Controller;

use Application\Controller\AbstractBaseController;
use Zend\View\Model\ViewModel;
use SettingQuery;
use Setting;

class SettingsController extends AbstractBaseController
{
	
	public function indexAction()
    {
    	$columns = [
    		'name' => [
    			'label' => 'SETTING_NAME',
    			'isLink' => false,
    			'canSort' => true,
    			'filter' => [
    				'type' => 'Text',
    				'match' => 'both',
    			],
    		],
    		'id' => [
    			'label' => 'SETTING_VALUE',
    			'helper' => function($id, $setting) {
    				return $setting->getValue();
    			}
    		],
    	];
    	
    	$table = $this->propelTableService;
    	$config = [
    		'columns' => $columns,
    		'linkRoute' => 'settings/view',
    		'linkRouteParams' => ['id' => 'id'],
    		'showEditLink' => true,
    		'sortOnLoad' => '+name',
    	];
    	
    	$table->setConfig($config)->setPropelModel(SettingQuery::create())->prepare();
    	
    	return new ViewModel([
    		'table' => $table,
    	]);
	}
	
	public function viewAction()
	{
		return new ViewModel([
		]);
	}
	
	public function editAction()
	{
		$id = (int)$this->params()->fromRoute('id');
		$setting = SettingQuery::create()->findPk($id);
		
		if (is_null($setting))
		{
			$this->flashMessenger()->addErrorMessage($this->translate('SETTING_NOT_FOUND_FOR_ID_X', [$id]));
			return $this->redirect()->toRoute('settings');
		}
		
		$form = $this->formManager->get('EditSetting');
		$form->bind($setting);
		
		if ($this->getRequest()->isPost())
		{
			$settingType = $setting->getType();
			$form->setData($this->getRequest()->getPost());
			
			if ($this->settingService->validate($form->get('value')->getValue(), $settingType))
			{
				try {
					$setting->setValue($form->get('value')->getValue(), $settingType)->save();
					$this->flashMessenger()->addSuccessMessage(sprintf($this->translate('SETTING_X_SAVED_OKAY'), $setting->getName()));
					return $this->redirect()->toRoute('settings');
				} catch (GenericException $e) {
					$this->flashMessenger()->addErrorMessage($this->translate('ERROR_SAVING_SETTING'));
				}
				
			} else {
				$this->flashMessenger()->addErrorMessage($this->translate('SETTING_VALUE_IS_INVALID'));
			}
			
		}
		
		return new ViewModel([
			'setting' => $setting,
			'form' => $form,
		]);
	}
	
}