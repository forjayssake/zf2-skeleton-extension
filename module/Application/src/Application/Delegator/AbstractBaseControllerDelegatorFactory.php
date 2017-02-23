<?php 
namespace Application\Delegator;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

class AbstractBaseControllerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Injects dependencies for AbstractBaseController during controller instantiation
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\DelegatorFactoryInterface::createDelegatorWithName()
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $controller = $callback();
        
        if ($serviceLocator instanceof ControllerManager) {
        	$controller->setTranslatorService($serviceLocator->getServiceLocator()->get('translator'));
        	$controller->setFormManager($serviceLocator->getServiceLocator()->get('FormElementManager'));
        	$controller->setPropelTableService($serviceLocator->getServiceLocator()->get('PropelTable'));
        	$controller->setConfig($serviceLocator->getServiceLocator()->get('config'));
        	$controller->setViewHelperManager($serviceLocator->getServiceLocator()->get('ViewHelperManager'));
        } elseif ($serviceLocator instanceof ServiceManager) {
            $controller->setTranslatorService($serviceLocator->get('translator'));
            $controller->setFormManager($serviceLocator->get('FormElementManager'));
            $controller->setPropelTableService($serviceLocator->get('PropelTable'));
            $controller->setConfig($serviceLocator->get('config'));
            $controller->setViewHelperManager($serviceLocator->get('ViewHelperManager'));
        }
        
        return $controller;
    }
}