<?php
namespace AccessManagement\Controller;

use Application\Controller\AbstractBaseController;
use AccessManagement\Form\AccessManagementForm ;
use AccessManagementLog;
use Zend\View\Model\ViewModel;

class AccessManagementController extends AbstractBaseController
{

    public function editAction()
    {
        $form  = new AccessManagementForm();

        if ($this->getRequest()->isPost())
        {
            $form->setData($this->getRequest()->getPost());
            $form->setValidationGroup($form->getName() . 'csrf');
            if ($form->isValid())
            {
                try {
                    AccessManagementLog::populateConfig($form);
                    $this->flashMessenger()->addSuccessMessage($this->translate('ACCESS_CONFIG_UPDATED_YES'));
                    return $this->redirect()->toRoute('access');
                } catch (\Exception $e) {
                    $this->flashMessenger()->addErrorMessage($this->translate('ACCESS_CONFIG_UPDATED_NO'));
                }
            } else {
                $this->flashMessenger()->addErrorMessage($this->translate('FORM_ERROR_GENERIC_MESSAGE'));
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }


}
