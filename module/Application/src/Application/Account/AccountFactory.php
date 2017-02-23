<?php
namespace Application\Account;

use User;
use RoleQuery;
use Application\Adapter\Authentication\Ldap;
use Application\Adapter\Authentication\AuthenticationException;
use \PropelException;

class AccountFactory
{
	/**
	 * Create a new account on authentication
	 * 
	 * @param array $config
	 * @param string $username
	 * @param string $password
	 * 
	 * @throws AuthenticationException
	 * 
	 * @return NULL|User
	 */
	public static function createAccount(array $config = [], $username, $password)
	{
		
		if(!array_key_exists('auto_create_account', $config['authentication']) || $config['authentication']['auto_create_account'] === false || count($config['authentication']['auto_create_account']) === 0)
			return null;
			
		$user = new User();
		$user->setUserName($username);
		
		foreach($config['authentication']['auto_create_account'] as $authType)
		{
			$user->setAuthenticationType($authType);
			
			switch($authType)
			{
				case User::AUTHENTICATE_LDAP:
					if(!array_key_exists('LdapAuthentication', $config))
						throw new AuthenticationException(__CLASS__ . '::' . __FUNCTION__ . 'Says: Must define `LdapAuthentication` in config.');
					
					//does user exist in LDAP?
					$ldap = new Ldap($user, $password);
					$ldap->setConfig($config);
					
					if($ldap->authenticate()->isValid())
						$adapter = new Adapter\Ldap($user,$config,$password);
					else
						continue;
					
			}
			
			if(!isset($adapter))
				return null;
			
			$user = $adapter->copyAccountDetails();
			
			$defaultRole = RoleQuery::create()->filterByisDefault(1)->findOne();
			if (is_null($defaultRole))
				return null;
			
			$user->set_role_id($defaultRole->getPrimaryKey());
				
			try
			{
				$user->save();
			}
			catch(PropelException $e)
			{
				return null;
			}
			
			return $user;
		}
	}
}