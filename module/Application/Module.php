<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Session\SessionManager;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use User;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Session\Container;


class Module
{
    public function onBootstrap(MvcEvent $e)
    {
    	$app = $e->getApplication();
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $serviceManager = $app->getServiceManager();

        $this->initAcl($e);

        $eventManager->attach(MvcEvent::EVENT_RENDER, function($e) {
        	$flashMessenger = new FlashMessenger();
        	$viewModel = $e->getViewModel();
        
        	if(get_class($viewModel) !== 'Zend\View\Model\ViewModel')
        		return;
        
        	$viewModel->setVariable('flashErrorMessages', $flashMessenger->getErrorMessages());
        	$viewModel->setVariable('flashInfoMessages', $flashMessenger->getInfoMessages());
        	$viewModel->setVariable('flashSuccessMessages', $flashMessenger->getSuccessMessages());
        	$viewModel->setVariable('flashWarningMessages', $flashMessenger->getWarningMessages());
        });

        $whitelist = $serviceManager->get('config')['authentication_exempt'];
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($e) use($whitelist) {
        	$match = $e->getRouteMatch();
        
        	//no route - this is a 404
        	if(!$match instanceof RouteMatch)
        		return;
        	
        	//user is logged in
        	$authService = new AuthenticationService();
			$matchRouteName = $match->getMatchedRouteName();
        	if($authService->hasIdentity()) {

        		$user = User::getUserFromIdentity($authService->getIdentity());

				if (!is_null($user))
        		{
					// check access management does not restrict access for this user
					$allowAccess = \AccessManagementLog::fetchAccessForUser($user);
					// user identity is still available on bootstrap when routing to logout - don't display user specific messages!
					if (!$allowAccess && $matchRouteName !== 'logout' && $matchRouteName !== 'login')
					{
						$this->setAccessManagementMessages($user, \AccessManagementLog::ROLE_MESSAGE_NAMESPACE);
						$authService->clearIdentity();
						header('Location: /login');
						exit;
					}

					// check user is allowed to access this route
	        		if(!in_array($match->getMatchedRouteName(), $whitelist)) {
	        			$this->checkAcl($e, $user, $matchRouteName);
        			}
        		}
        		return;
        	} else {
				$this->setAccessManagementMessages();
			}

        	//page is in non-authenticated whitelist
        	if(in_array($matchRouteName, $whitelist))
        		return;

        	//destroy old session if it has exipered. ensures that a new session cookie is sent
        	if(getenv('APPLICATION_ENV') !== 'test')
        	{
        		$sm = new SessionManager();
        		$sm->regenerateId(true)->destroy();
        	}
        	
        	// user should not be here - redirect to login page!
        	header('Location: /login');
        	exit;
        });
        
    }

	public function setAccessManagementMessages(User $user = null, $namespace = null)
	{
		if (is_null($namespace))
			$namespace = \AccessManagementLog::ALL_USER_MESSAGE_NAMESPACE;

		$container = new Container($namespace);
		$messages = \AccessManagementLog::fetchAccessMessages($user, (is_null($user) ? false : true) );
		$container->messages = $messages;
	}


    public function initAcl(MvcEvent $e)
    {
    	$acl = new Acl();
    	$roles = include __DIR__ . '/config/application.acl.roles.php';
    	$allResources = array();
    	foreach ($roles as $role => $resources) {
    	
    		$role = new GenericRole($role);
    		$acl->addRole($role);
    	
    		$allResources = array_merge($resources, $allResources);

    		foreach ($resources as $resource) {
    			if(!$acl ->hasResource($resource))
    				$acl->addResource(new GenericResource($resource));
    		}

    		foreach ($allResources as $resource) {
    			$acl->allow($role, $resource);
    		}
    	}
    	
    	//setting to view
    	$e -> getViewModel()->acl = $acl;
    }
    
    
    public function checkAcl(MvcEvent $e, User $user, $routeName)
    {
    	// system administrators can access-management-management any part of the application 
    	if ($user->getsystemAdministrator())
    		return;
    	
    	$userRole = $user->getRole()->getConstant();
    	if (!$e->getViewModel()->acl->isAllowed($userRole, $routeName)) {
    		$response = $e -> getResponse();
    		$response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/403');
    		$response->setStatusCode(403);
    	}
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getFormElementConfig()
    {
    	return array(
    		'invokables' => [
    			'LoginForm' => 'Application\Form\LoginForm',
				'CKEditorElement' => 'Application\Form\Element\CKEditorElement',
    		]
    	);
    }
}