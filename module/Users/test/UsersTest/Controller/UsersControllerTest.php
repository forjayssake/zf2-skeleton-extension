<?php

namespace UsersTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class UsersControllerTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		$this->setApplicationConfig(
				include './config/application.config.php'
				);
		parent::setUp();
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testIndexActionCanBeAccessed()
	{
		$this->dispatch('/users');
		$this->assertResponseStatusCode(200);
	
		$this->assertModuleName('Users');
		$this->assertControllerName('Users\Controller\UsersController');
		$this->assertControllerClass('UsersController');
		$this->assertMatchedRouteName('users');
	}
}