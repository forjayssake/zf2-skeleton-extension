<?php
namespace Settings\Service;

use Zend\Di\ServiceLocatorInterface;
use SettingQuery;
use Setting;

class SettingService
{
	/**
	 * 
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}
	
	/**
	 * return a setting value for a given $name constant
	 * @param string $name
	 */
	public function getSetting($name)
	{
		$setting = SettingQuery::create()->findOneByName($name);
		
		if (is_null($setting))
			return null;
		
		return $setting->getValue();
	}
	
	public function validate($value, $type)
	{
		switch ($type)
		{
			case Setting::TYPE_INT :
				if (is_numeric($value))
				{
					$value = $value + 0;
					return is_int($value);
				}
				break;
			case Setting::TYPE_FLOAT :
				if (is_numeric($value))
				{
					$value = $value + 0;
					return is_float($value);
				}
				break;
			case Setting::TYPE_STRING :
				return is_string($value);
				break;
			case Setting::TYPE_BOOL :
				if (is_bool($value))
				{
					return true;
				} elseif ($value === 0 || $value === 1) {
					return true;
				} else {
					return false;
				}
				
				break;
			default : 
				return false;
				break;
		}
	}
	

}