<?php
namespace Email\Factory;

use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Email\Form\AddEditTemplate;

class AddEditTemplateFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $templateEventOptions = [];
        if (isset($config['template_options']) && count($config['template_options']) > 0)
        {
            foreach($config['template_options'] as $event => $details)
            {
                $templateEventOptions[$event] = $event;
            }
        }

        if (count($templateEventOptions) == 0)
            throw new Exception(__CLASS__ .'::' . __FUNCTION__ . ' Says: No valid template events have been configured');

        $form = new AddEditTemplate($templateEventOptions);
        $form->init();

        return $form;
    }
}