<?php
namespace Application\Account\Adapter;

use User;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\Ldap\Exception\LdapException;

class Ldap
{
	private $user, $password, $config;
	
	public function __construct(User $user, array $config = [], $password = null)
	{
		$this->user = $user;
		$this->password = $password;
		$this->config = $config;
	}
	
	/**
	 * Retrieve LDAP account details for user with the specified username
	 * @return array LDAP account details
	 */
	protected function getLdapAccountDetails()
	{
		$ldap = new ZendLdap($this->config['LdapAuthentication']);
		
		//try binding to access-management-management more user details if a password has been provided
		if($this->password !== null)
		{
			try
			{
				$ldap->bind($this->user->getUsername(),$this->password);
			} // if bind fails we can still fallback to anon access-management-management
			catch(LdapException $e){}
		}
		
		return $ldap->getEntry('uid='.$this->user->getUsername().','.$this->config['LdapAuthentication']['username']);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Users\Account\Adapter.AdapterInterface::copyAccountDetails()
	 */
	public function copyAccountDetails()
	{
		$ldapDetails = $this->getLdapAccountDetails();
		
		if(!is_array($ldapDetails))
			return $this->user;
		
		foreach($this->config['authentication']['auto_create_account_mapping'][User::AUTHENTICATE_LDAP] as $objectName => $attributes)
		{
			if($objectName === 'User')
				$object = $this->user;
			else
			{
				$className = $objectName;
				$methodName = 'add'.$objectName;
				
				if(class_exists($className) && method_exists($className, $methodName))
					$object = new $className();
				else
					continue;
			}
			
			foreach($attributes as $objectAttribute => $ldapAttribute)
			{
				if(array_key_exists($ldapAttribute, $ldapDetails))
				{
					if(is_array($ldapDetails[$ldapAttribute]) && count($ldapDetails[$ldapAttribute]) === 1)
						$value = array_shift($ldapDetails[$ldapAttribute]);
					elseif(is_string($ldapDetails[$ldapAttribute]) && strlen($ldapDetails[$ldapAttribute]) > 0)
						$value = $ldapDetails[$ldapAttribute];
					else
						continue;
					
					$attributeMethod = 'set'.ucfirst($objectAttribute);
					$object->$attributeMethod($value);
				}
			}
			
			if($objectName !== 'User')
				$this->user->$methodName($object);
				
		}
		
		return $this->user;
	}
}