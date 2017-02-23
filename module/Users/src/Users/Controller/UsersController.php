<?php
namespace Users\Controller;

use Application\Controller\AbstractBaseController;
use Zend\View\Model\ViewModel;
use User;
use UserQuery;
use Role;
use Application\Form\DeleteCheck;
use Zend\InputFilter\InputFilter;

class UsersController extends AbstractBaseController
{
    
	public function indexAction()
    {
    	$columns = [
    		'id' => [
    			'label' => 'ID',
    			'isLink' => true,
    			'canSort' => true,
    			'filter' => [
    				'type' => 'Text',
    				'match' => 'exact'
    			],
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
    		'email' => [
    			'label' => 'EMAIL',
    			'canSort' => true,
    			'filter' => [
    				'type' => 'Text',
    				'match' => 'both'
    			],
				'helper' =>[
					'name' => 'RenderEmail',
					'params' => [true, false]
				],
    		],
    		'username' => [
    			'label' => 'USERNAME',
    			'canSort' => true,
    			'filter' => [
    				'type' => 'Text',
    				'match' => 'both'
    			],
    		],
    		'_role_id' => [
    			'label' => 'ROLE',
    			'canSort' => true,
				'helper' => function ($value, $object) {
					return $object->getRole()->getName();
				},
				'filter' => [
					'type' => 'Select',
					'match' => 'exact',
					'options' => [
						'empty_option' => 'PLEASE_SELECT_SHORT',
						'value_options' => Role::getRoleSelectOptions(),
					],
				],
    		],
    		'systemAdministrator' => [
    			'label' => 'SYSTEM_ADMINISTRATOR',
    			'canSort' => true,
    			'helper' => [
    				'name' => 'RenderTickCrossNull',
    			],
    			'filter' => [
    				'type' => 'Select',
    				'match' => 'exact',
    				'options' => [
    					'empty_option' => '--',
    					'value_options' => [
    						1 => 'Yes',
    						0 => 'No',
    					],
    				],
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
    	 
    	$table->setConfig($config)->setPropelModel(UserQuery::create());

    	$table->prepare();

    	return new ViewModel([
			'table' => $table,
		]);
    }
    
    public function viewAction()
    {
    	$id = $this->params()->fromRoute('id');
    	$user = UserQuery::create()->findPk($id);
    	
    	if (is_null($user))
    	{
    		$this->flashMessenger()->addErrorMessage(sprintf($this->translate('USER_NOT_FOUND_ID_X'), $id));
    		return $this->redirect()->toRoute('users');
    	}
    	
    	return new ViewModel([
    		'user' => $user,
    	]);
    }
    
    public function addAction()
    {
		$form = $this->serviceLocator->get('AddEditUserForm');
    	$user = new User();
    	$form->bind($user);

    	if ($this->getRequest()->isPost())
    	{
    		$form->setData($this->getRequest()->getPost());
    		 
    		if ($form->isValid())
    		{
    			try {
    				$user->save();
    				
    				if ($user->getAuthenticationType() == User::AUTHENTICATE_DB)
    				{
    					$this->flashMessenger()->addSuccessMessage($this->translate('SAVING_USER_X_SUCCESS_ADD_PASSWORD', [$user->getFullName()]));
    					return $this->redirect()->toRoute('users/view/actions', ['action' => 'password', 'id' => $user->getPrimaryKey()]);
    				} else {
	    				$this->flashMessenger()->addSuccessMessage($this->translate('SAVING_USER_X_SUCCESS', [$user->getFullName()]));
    					return $this->redirect()->toRoute('users/view', ['action' => 'view', 'id' => $user->getPrimaryKey()]);
    				}
    			} catch (GenericException $e) {
    				$this->flashMessenger()->addErrorMessage($this->translate('SAVING_USER_X_FAILED', [$user->getGenera()]));
    			}
    		} else {
    			$this->flashMessenger()->addErrorMessage($this->translate('FORM_ERRORS'));
    		}
    	}
    	
    	return new ViewModel([
    		'form' => $form,
    	]);
    }
    
    public function editAction()
    {
    	$id = $this->params()->fromRoute('id');
    	$user = UserQuery::create()->findPk($id);
    	 
    	if (is_null($user))
    	{
    		$this->flashMessenger()->addErrorMessage(sprintf($this->translate('USER_NOT_FOUND_ID_X'), $id));
    		return $this->redirect()->toRoute('users');
    	}

    	$form = $this->serviceLocator->get('AddEditUserForm');
    	$form->bind($user);

    	if ($this->getRequest()->isPost())
    	{
    		$form->setData($this->getRequest()->getPost());
    		 
    		if ($form->isValid())
    		{
    			try {
    				$user->save();
    				$this->flashMessenger()->addSuccessMessage($this->translate('SAVING_USER_X_SUCCESS', [$user->getFullName()]));
    				return $this->redirect()->toRoute('users/view', ['action' => 'view', 'id' => $user->getPrimaryKey()]);
    			} catch (GenericException $e) {
    				$this->flashMessenger()->addErrorMessage($this->translate('SAVING_USER_X_FAILED', [$user->getFullName()]));
    			}
    		} else {
    			$this->flashMessenger()->addErrorMessage($this->translate('FORM_ERRORS'));
    		}
    	}

    	return new ViewModel([
    		'user' => $user,
    		'form' => $form,
    	]);
    }
    
    public function deleteAction()
    {
    	$id = $this->params()->fromRoute('id');
    	$user = UserQuery::create()->findPk($id);
    	 
    	if (is_null($user))
    	{
    		$this->flashMessenger()->addErrorMessage(sprintf($this->translate('USER_NOT_FOUND_ID_X'), $id));
    		return $this->redirect()->toRoute('users');
    	}
    	
    	$userDetail = $user->getFullName() . ' (' . $user->getUsername() . ')';
    	$form = new DeleteCheck();
    	
    	if ($this->getRequest()->isPost())
    	{
    		$data = $this->getRequest()->getPost();
    		if ($form->canDelete($data))
    		{
    			try {
    				$user->delete();
    				$this->flashMessenger()->addSuccessMessage($this->translate('DELETED_USER_X_SUCCESS', [$userDetail]));
    				return $this->redirect()->toRoute('users');
    			} catch (GenericException $e) {
    				$this->flashMessenger()->addErrorMessage($this->translate('ERROR_DELETING_USER_X', [$userDetail]));
    			}
    		} else {
    			return $this->redirect()->toRoute('users/view', ['action' => 'view', 'id' => $user->getPrimaryKey()]);
    		}
    	}
    	
    	return new ViewModel([
    		'user' => $user,
    		'form' => $form,
    		'userDetail' => $userDetail,
    	]);
    }

    public function passwordAction()
    {
    	$id = $this->params()->fromRoute('id');
    	$user = UserQuery::create()->findPk($id);
    	
    	if (is_null($user))
    	{
    		$this->flashMessenger()->addErrorMessage(sprintf($this->translate('USER_NOT_FOUND_ID_X'), $id));
    		return $this->redirect()->toRoute('users');
    	}
    	
    	if ($user->getAuthenticationType() !== User::AUTHENTICATE_DB)
    	{
    		$this->flashMessenger()->addErrorMessage(sprintf($this->translate('USER_X_NOT_DB_AUTHENTICATION'), $user->getFullName()));
    		return $this->redirect()->toRoute('users/view', ['id' => $user->getPrimaryKey()]);
    	}
    	
    	$form = $this->formManager->get('PasswordForm');
    	
    	$inputFilter = new InputFilter();
    	$inputFilter->add(['name' => 'password', 'required' => 'true']);
    	$inputFilter->add(['name' => 'confirm_password', 'required' => 'true']);
    	$form->setInputFilter($inputFilter);
    	
    	if ($this->getRequest()->isPost())
    	{
    		$form->setData($this->getRequest()->getPost());
    		if ($form->isValid())
    		{
    			$passwordCheck = $this->checkPasswordConstraints(
    					$form->get('password')->getValue(),
    					$form->get('confirm_password')->getValue()
    				);
				
    			if (!is_array($passwordCheck) && $passwordCheck === true)
    			{
    				// bcrypt password
    				$passwordHash = User::createPasswordHash($form->get('password')->getValue());
    				$user->setPassword($passwordHash)->save();
    				$this->flashMessenger()->addSuccessMessage($this->translate('PASSWORD_SAVED_SUCCESSFULLY'));
    				return $this->redirect()->toRoute('users/view', ['id' => $user->getPrimaryKey()]);
    			} else {
    				$errorMessage = $this->generatePasswordErrorMessage($passwordCheck);
    				$this->flashMessenger()->addErrorMessage($errorMessage);
    			}
    		}
    	}
    	
    	return new ViewModel([
    		'user' => $user,
    		'form' => $form,
    	]);
    }
    
    /**
     * format errors messages for a password constraints check failure
     * @param array $messages
     * @return string
     */
    private function generatePasswordErrorMessage(array $messages = [])
    {
    	$strMessage = $this->translate('PASSWORD_FAILED_CONSTRAINTS') . '<ul>';
    	foreach($messages as $message)
    	{
    		$strMessage .= '<li>' . $this->translate($message) . '</li>';
    	}
    	$strMessage .= '</ul>';
    	
    	return $strMessage;
    }
    
    /**
     * determine whether a password matches constraints
     * returns true on success or array of error messages
     * @param string $password1
     * @param string $password2
     * @return bool|array
     */
    private function checkPasswordConstraints($password1, $password2)
    {
    	if ($password1 !== $password2)
    		return ['PASSWORDS_DO_NOT_MATCH'];
    	
    	return User::checkPasswordConstraints($password1);
    }
    
}
