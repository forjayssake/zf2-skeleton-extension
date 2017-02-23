<?php
namespace Application\Factory;

use Application\Service\PropelTableService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Cache\Storage\Adapter\Session;
use Zend\Cache\Storage\Adapter\SessionOptions;
use Zend\Session\Container as SessionContainer;

class PropelTableServiceFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
    	$table = new PropelTableService($serviceLocator);
    	$table->setRequestObject(new Request());
    	
    	$options = new SessionOptions();
    	$options->setSessionContainer(new SessionContainer('propel_table_storage'));
    	$table->setStorage(new Session($options));
    	
    	return $table;
    }
}