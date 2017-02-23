<?php
namespace Application\Adapter\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Authentication\Result;

class Database implements AdapterInterface
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
	
	public function __construct(User $user, $password)
	{
		$this->user = $user;
		$this->password = $password;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\Authentication\Adapter.AdapterInterface::authenticate()
	 */
	public function authenticate()
	{
		$bcrypt = new Bcrypt();
		if($bcrypt->verify($this->password, $this->user->getPassword()))
			return new Result(Result::SUCCESS, $this->user->getSessionIdentity());
		else
			return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->user->getSessionIdentity(), ['Password is invalid']);
	}
	
}