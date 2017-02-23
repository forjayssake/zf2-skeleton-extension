<?php
namespace Application\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class SetupNavigationFactory extends DefaultNavigationFactory
{
	protected function getName()
	{
		return 'setup';
	}
}