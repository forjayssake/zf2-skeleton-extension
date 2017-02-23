<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\I18n\Translator;
use User;
use Application\Service\PropelTableService;
use Zend\Form\FormElementManager;
use Zend\InputFilter\InputFilterInterface;
use Zend\Form\Form as ZendForm;
use Application\Exception\GenericException;

abstract class AbstractBaseController extends AbstractActionController
{
	/**
	 *
	 * @var Zend\Mvc\I18n\Translator
	 */
	protected $translator;
	
	/**
	 *
	 * @var User
	 */
	protected $loggedInUser;
	
	/**
	 * $loggedInUser role constant
	 * @var string
	 */
	protected $loggedInRole;
	
	/**
	 * 
	 * @var FormElementManager
	 */
	protected $formManager;

	/**
	 * 
	 * @var array | ArrayIterableObject
	 */
	public $config;
	
	/**
	 * 
	 * @var PropelTableService
	 */
	public $propelTableService;
	
	/**
	 * 
	 * @var ViewHelperManager
	 */
	public $viewHelperManager;

	
	public function __construct()
	{
		$this->loggedInUser = User::getLoggedIn();
		$this->loggedInRole = $this->loggedInUser->getRole()->getConstant();
	}
	
	
	public function setViewHelperManager($viewHelperManager)
	{
		$this->viewHelperManager = $viewHelperManager;
		return $this;
	}
	
	public function setConfig($config)
	{
		$this->config = $config;
		return $this;
	}
	
	public function setFormManager(FormElementManager $formManager)
	{
		$this->formManager = $formManager;
		return $this;
	}

	public function setPropelTableService(PropelTableService $propelTableService)
	{
		$this->propelTableService = $propelTableService;
		return $this;
	}
	
	/**
	 * set the Translator service
	 * @param Translator $translator
	 * 
	 * @return AbstractBaseController
	 */
	public function setTranslatorService(Translator $translator)
	{
		$this->translator = $translator;
		return $this;
	}
	
	/**
	 * translate() wrapper
	 * @param string $message message to translate
	 * @param array $parts optional array of placeholder arguments
	 * 
	 * @return string
	 */
	public function translate($message, array $parts = [])
	{
		if (count($parts) > 0)
		{
			return vsprintf($this->translator->translate($message), $parts);
		} else {
			return $this->translator->translate($message);
		}
	}

	/**
	 * a generic, optional method to handle most common form processing from POST data
	 *
	 * @param ZendForm $form
	 * @param object $object Propel object bound to the form
	 * @param array $successRoute route to redirect to on successful save ['route' => 'routeString', 'params' => ['param1' => param1, ...]] ('id' always parsed)
	 * @param string $successMessage translation string to parse to flashMessanger on save success
	 * @param string $failureMessage translation string to parse to flashMessanger on save failure
	 * @param array $validatilonGroup fields to validate (default to all)
	 *
	 * return void
	 */
	protected function processForm(ZendForm $form, $object, array $successRoute, $successMessage = null, $failureMessage = null, array $validatilonGroup = [])
	{
		if (!$this->getRequest()->isPost())
			return;

		if (is_null($successMessage)) {
			$successMessage = 'GENERIC_SAVE_SUCCESS';
		}

		if (is_null($failureMessage)) {
			$failureMessage = 'GENERIC_SAVE_FAILURE';
		}

		if (count($validatilonGroup) > 0)
		{
			$form->setValidationGroup($validatilonGroup);
		}

		$data = $this->getRequest()->getPost();
		$form->setData($data);

		if ($form->isValid())
		{
			try {
				$object->save();
				$successRoute['params']['id'] = $object->getPrimaryKey();
				$this->flashMessenger()->addSuccessMessage($this->translate($successMessage));
				return $this->redirect()->toRoute($successRoute['route'], $successRoute['params']);
			} catch (\Exception $e) {
				$this->flashMessenger()->addErrorMessage($this->translate($failureMessage));
			}
		} else {
			$this->flashMessenger()->addErrorMessage($this->translate('FORM_ERROR_GENERIC_MESSAGE'));
		}

	}


}
