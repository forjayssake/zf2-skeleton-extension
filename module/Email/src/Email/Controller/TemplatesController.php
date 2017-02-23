<?php
namespace Email\Controller;

use Application\Controller\AbstractBaseController;
use TemplateQuery;
use Template;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Form\DeleteCheck;
use Email\Assertion\AssertUserCanDeleteTemplate;

class TemplatesController extends AbstractBaseController
{

    private function validateConfig()
    {
        if (!isset($this->config['template_options']))
            throw new GenericException(__CLASS__ . '::' . __FUNCTION__ . ' Says: A template_options key must be defined in module-config.php to enable this module');

        if (count($this->config['template_options']) === 0) {
            $this->flashMessenger()->addWarningMessage($this->translate('NO_TEMPLATE_EVENTS_WARNING'));
        }
    }

    public function indexAction()
    {
        $this->validateConfig();

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
            'name' => [
                'label' => 'NAME',
                'isLink' => true,
                'canSort' => true,
                'filter' => [
                    'type' => 'Text',
                    'match' => 'both'
                ],
            ],
            'event' => [
                'label' => 'EVENT',
                'canSort' => true,
                'filter' => [
                    'type' => 'Text',
                    'match' => 'both'
                ],
            ],
            'subject' => [
                'label' => 'SUBJECT',
                'canSort' => true,
                'filter' => [
                    'type' => 'Text',
                    'match' => 'both'
                ],
            ],
            'body' => [
                'label' => 'BODY_CONTENT',
                'canSort' => true,
                'filter' => [
                    'type' => 'Text',
                    'match' => 'both'
                ],
                'helper' => [
                    'name' => 'StringTruncate',
                    'params' => [100, '...']
                ],
            ],
        ];

        $table = $this->propelTableService;
        $config = [
            'columns' => $columns,
            'linkRoute' => 'templates/view',
            'linkRouteParams' => ['id' => 'id'],
            'showEditLink' => true,
            'showDeleteLink' => $this->loggedInUser->getsystemAdministrator(),
            'sortOnLoad' => '+id',
        ];

        $table->setConfig($config)->setPropelModel(TemplateQuery::create());

        $table->prepare();

        return new ViewModel([
            'table' => $table,
        ]);



    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $template = TemplateQuery::create()->findPk($id);
        if (is_null($template))
        {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('TEMPLATE_X_NOT_FOUND'), $id));
            return $this->redirect()->toRoute('templates');
        }

        return new ViewModel([
            'template' => $template,
        ]);
    }

    public function addAction()
    {
        $this->validateConfig();

        $form = $this->serviceLocator->get('AddEditTemplateForm');
        $template = new Template();
        $form->bind($template);

        $this->processForm($form, $template, ['route' => 'templates/view', 'params' => []], 'TEMPLATE_ADDED_YES', 'TEMPLATE_ADDED_NO');

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function editAction()
    {
        $this->validateConfig();

        $id = $this->params()->fromRoute('id');
        $template = TemplateQuery::create()->findPk($id);
        if (is_null($template))
        {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('TEMPLATE_X_NOT_FOUND'), $id));
            return $this->redirect()->toRoute('templates');
        }

        $form = $this->serviceLocator->get('AddEditTemplateForm');
        $form->bind($template);

        $this->processForm($form, $template, ['route' => 'templates/view', 'params' => []], 'TEMPLATE_UPDATED_YES', 'TEMPLATE_UPDATED_NO');

        return new ViewModel([
            'form' => $form,
            'template' => $template,
        ]);
    }

    public function deleteAction()
    {
        $canDelete = new AssertUserCanDeleteTemplate($this->loggedInUser);
        if (!$canDelete->assert())
        {
            $this->flashMessenger()->addErrorMessage($this->translate('GENERIC_403'));
            return $this->redirect()->toRoute('templates');
        }

        $id = $this->params()->fromRoute('id');
        $template = TemplateQuery::create()->findPk($id);
        if (is_null($template))
        {
            $this->flashMessenger()->addErrorMessage(sprintf($this->translate('TEMPLATE_X_NOT_FOUND'), $id));
            return $this->redirect()->toRoute('templates');
        }

        $templateName  = $template->getname();
        $form = new DeleteCheck();

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if ($form->canDelete($data))
            {
                try {
                    $template->delete();
                    $this->flashMessenger()->addSuccessMessage($this->translate('DELETED_TEMPLATE_X_SUCCESS', [$templateName]));
                    return $this->redirect()->toRoute('templates');
                } catch (GenericException $e) {
                    $this->flashMessenger()->addErrorMessage($this->translate('ERROR_DELETING_TEMPLATE_X', [$templateName]));
                }
            } else {
                return $this->redirect()->toRoute('templates/view', ['action' => 'view', 'id' => $id]);
            }
        }

        return new ViewModel([
            'form' => $form,
            'template' => $template,
        ]);
    }

    /**
     * return a json encoded array of available parameters for a given template
     *  primarily used via ajax
     *
     * @return JsonModel
     * @throws GenericException
     */
    public function fetchEventParametersAction()
    {
        $parameters = [];

        $request = $this->getRequest();

        if ($request->isPost())
        {
            $data = $request->getPost();
            if (!isset($data->eventname) || is_null($data->eventname) || strlen($data->eventname) === 0)
            {
                throw new GenericException(__CLASS__ . '::' . __FUNCTION__ . ' Says: A event name must be supplied');
            }
        }

        if (!isset($this->config['template_options'][$data->eventname]))
            throw new GenericException(__CLASS__ . '::' . __FUNCTION__ . ' Says: Event: `' . $data->eventname . '` has not been configured');

        if (isset($this->config['template_options'][$data->eventname]['params']))
        {
            foreach($this->config['template_options'][$data->eventname]['params'] as $paramName => $details)
            {
                $parameters[$paramName] = ['display' => $details['display']] ;
            }
        }

        $result = new JsonModel([
            'parameters' => $parameters,
        ]);

        return $result;
    }
}