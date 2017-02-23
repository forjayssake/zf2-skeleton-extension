<?php
namespace Users\Factory;

use Application\Service\PropelTableService;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\PhpEnvironment\Request;
use Users\Form\AddEditUser;
use User;

class AddEditUserFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $authenticationOptions = [];
        if (isset($config['authentication']) && isset($config['authentication']['authentication_methods']))
        {
            foreach($config['authentication']['authentication_methods'] as $option)
            {
                if (isset(User::$authenticationTypes[$option]))
                {
                    $authenticationOptions[$option] = User::$authenticationTypes[$option];
                }
            }
        }

        if (count($authenticationOptions) == 0)
            throw new Exception(__CLASS__ .'::' . __FUNCTION__ . ' Says: No valid authentication methods have been configured');

        $form = new AddEditUser($authenticationOptions);
        $form->init();

        return $form;
    }
}