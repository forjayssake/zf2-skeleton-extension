<?php
namespace Application\Adapter\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\Ldap\Exception\LdapException;
use Zend\Authentication\Result;
use User;
use Application\Account\Adapter\Ldap as UserLdap;
use PropelException;

class Ldap implements AdapterInterface
{
	/**
	 * 
	 * @var User
	 */
	protected $user;
	
	/**
	 * 
	 * @var string
	 */
	protected $password;
	
	/**
	 * 
	 * @var ZendLdap;
	 */
	protected $ldap;
	
	/**
	 * 
	 * @var array
	 */
	protected $config = [];
	
	
	public function __construct(User $user, $password)
	{
		$this->user = $user;
		$this->password = $password;
	}
	
	public function setConfig(array $config = [])
	{
		$this->config = $config;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\Authentication\Adapter.AdapterInterface::authenticate()
	 */
	public function authenticate()
	{
		$this->connectToLDAPServer();
		
		try
		{
			$this->ldap->bind($this->user->getUsername(),$this->password);
			
			if($this->config['authentication']['sync_ldap_details_on_login'] === true)
			{
				$userLdap = new UserLdap($this->user,$this->config,$this->password);
				$this->user = $userLdap->copyAccountDetails();
				$this->user->save();
			}
		}
		catch(LdapException $e)
		{
			switch($e->getCode())
			{
				case LdapException::LDAP_NO_SUCH_OBJECT:
					return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $this->user->getSessionIdentity(), ['LDAP_ERROR_20']);
				case LdapException::LDAP_INVALID_CREDENTIALS:
					return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->user->getSessionIdentity(), ['LDAP_ERROR_31']);
				case LdapException::LDAP_UNWILLING_TO_PERFORM:
					return new Result(Result::FAILURE, $this->user->getSessionIdentity(), ['LDAP_ERROR_35']);
				default:
					return new Result(Result::FAILURE_UNCATEGORIZED, $this->user->getSessionIdentity(), ['LDAP Error: '.$e->getMessage()]);
			}
		}
		catch(PropelException $e)
		{
			//if an ldap sync has occured and it has failed to find the required data then this can result in a propel exception
			return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $this->user->getSessionIdentity(), ['LDAP_ERROR_20']);
		}
		
		return new Result(Result::SUCCESS,$this->user->getSessionIdentity());
	}
	
	/**
	 * Connect to an LDAP server
	 * @throws AuthenticationException
	 */
	private function connectToLDAPServer()
	{
		if(!array_key_exists('baseDn',$this->config['LdapAuthentication']) || strlen($this->config['LdapAuthentication']['baseDn']) === 0)
			throw new AuthenticationException('Must define `baseDn`. See: SwitchSystems\Adapter\Authentication\Ldap');
		
		if(!array_key_exists('host',$this->config['LdapAuthentication']) || strlen($this->config['LdapAuthentication']['host']) === 0)
			throw new AuthenticationException('Must define `host`. See: SwitchSystems\Adapter\Authentication\Ldap');
		
		$this->ldap = new ZendLdap($this->config['LdapAuthentication']);
	}
	
}