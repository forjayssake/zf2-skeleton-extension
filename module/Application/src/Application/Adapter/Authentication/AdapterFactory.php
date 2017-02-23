<?php
namespace Application\Adapter\Authentication;

use User;
use UserQuery;
use Zend\Authentication\Result;
use Application\Account\AccountFactory;

class AdapterFactory
{
	
	/**
	 * Return appropriate authentication adapater for a given username/password
	 * @param array $config Application config array - used for retrieving adapter specific settings
	 * @param string $username
	 * @param string $password If no password is specified request must be from SSO
	 * @return Zend\Authentication\Adapter\AdapterInterface
	 */
	public static function getAdapter(array $config = [], $username, $password)
	{
		$user = UserQuery::create()->filterByEmail($username)->_or()->filterByUsername($username)->findOne();
		
		// if user is not in the database should we try and create it?
		if($user === null && !(array_key_exists('auto_create_account',$config['authentication']) && count($config['authentication']['auto_create_account']) > 0))
			return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, ['User not found']);
		elseif($user === null)
		{
			//attempt user account creation
			$user = AccountFactory::createAccount($config, $username, $password);
			if($user === null)
				return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $username, ['User not found']);
		}
		
		// has the user account been archived (deleted effectively)
		if($user->getArchive() !== null)
			return new Result(Result::FAILURE_UNCATEGORIZED, $username, ['Account has been deleted']);
		
		//check which authentication type the user is configured to use and create an adapter
		switch($user->getAuthenticationType())
		{
			default:
			case User::AUTHENTICATE_DB:
				
				return new Database($user,$password);
				
			case User::AUTHENTICATE_LDAP:
				
				if(!array_key_exists('LdapAuthentication', $config))
					throw new AuthenticationException(__CLASS__ . '::' . __FUNCTION__ . 'Says: Must define `LdapAuthentication` in config.');
				
				$ldap = new Ldap($user,$password);
				$ldap->setConfig($config);
				
				return $ldap;
		}
	}
}