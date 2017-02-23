<?php
namespace Application\Assertion;

use User;

class AssertUserInRoleHeirarchy
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
	protected $role;
	
	/**
	 *
	 * @var string
	 */
	protected $checkRole;
	
	public function __construct(User $user, $checkRole = null)
	{
		$this->user = $user;
		$this->role = $user->getRole()->getconstant();
		$this->checkRole = $checkRole;
	}
	
	public function assert()
	{
		// if no base role is defined anyone can see this menu item
		if (is_null($this->checkRole)){
			return true;
		}
		
		$roles = include __DIR__ . '/../../../config/application.acl.roles.php';
		
		$checkRolePosition = array_search($this->checkRole, array_keys($roles));
		$userRolePosition = array_search($this->role, array_keys($roles));
		return ($userRolePosition >= $checkRolePosition);
	}
	
}