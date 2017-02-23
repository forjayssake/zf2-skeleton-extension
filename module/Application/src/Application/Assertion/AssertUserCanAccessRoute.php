<?php
namespace Application\Assertion;

use User;

class AssertUserCanAccessRoute
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
	protected $route;
	
	public function __construct(User $user, $route)
	{
		$this->user = $user;
		$this->route = $route;
	}
	
	public function assert()
	{
		// system administrators can view all routes by default
		if ($this->user->getsystemAdministrator())
			return true;
		
		$roles = include __DIR__ . '/../../../config/application.acl.roles.php';
		$userRole = $this->user->getRole()->getconstant();
		
		$availableRoutes = [];
		foreach($roles as $role => $routes)
		{
			foreach($routes as $route)
			{
				$availableRoutes[] = $route;
			}
			
			// the acl list is effectively a hierarchy - once we hit the users role exit the loop
			if ($userRole === $role) {
				break;
			}
		}
		
		if (count($availableRoutes) > 0 && in_array($this->route, $availableRoutes)) {
			return true;
		}
		
		return false;
	}
	
}