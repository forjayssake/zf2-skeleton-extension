<?php
namespace Email\Factory;

use Symfony\Component\Config\Definition\Exception\Exception;
use Email\View\Helper\RenderTemplateParameters;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RenderTemplateParametersFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('config');
        
        if (!isset($config['template_options']) || count($config['template_options']) === 0)
        {
            throw new Exception(__CLASS__ .'::' . __FUNCTION__ . ' Says: No template events have been configured');
        }

        $helper = new RenderTemplateParameters($config['template_options']);

        return $helper;
    }
}