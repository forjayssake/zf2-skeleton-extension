<?php

use Base\User as BaseUser;
use Zend\Authentication\AuthenticationService;
use Zend\Crypt\Password\Bcrypt;
use Base\CampusLocationQuery;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Skeleton subclass for representing a row from the 'users' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class User extends BaseUser implements InputFilterAwareInterface
{
	use \Application\Db\AuditableDataTrait;
	
	/**
	 *
	 * @var InputFilter $inputFilter
	 */
	protected $inputFilter;
	
	/**
	 *
	 * @var string
	 */
	protected $objectIcon = 'fa fa-user';
	
	const AUTHENTICATE_DB = 0;
	const AUTHENTICATE_LDAP = 1;
	const MIN_PASSWORD_LENGTH = 8;
	
	const PASSWORD_TOO_SHORT = 0;
	const PASSWORD_UPPERCASE = 1;
	const PASSWORD_NUMERIC = 2;
	const PASSWORD_SPECIAL = 3;
	
	/**
	 * password components - password should contain one from each character set
	 * @var array
	 */
	public static $passwordComponents = [
			self::PASSWORD_UPPERCASE => 'ABCDEFGHIGJLMNOPQRSTUVWXYZ',
			self::PASSWORD_NUMERIC => '1234567890',
			self::PASSWORD_SPECIAL => '!�$%^& &*()-_=+|\/,.#~@;:`��'
	];
	
	/**
	 * authentication types
	 * @var array
	 */
	public static $authenticationTypes = [
			self::AUTHENTICATE_DB => 'AUTHENTICATION_DB',
			self::AUTHENTICATE_LDAP => 'AUTHENTICATE_LDAP'
	];
	
	/**
	 * Password failed constraints error messages
	 * @var array
	 */
	public static $passwordErrors = [
			self::PASSWORD_UPPERCASE => 'PASSWORD_FAILED_UPPER_CASE',
			self::PASSWORD_NUMERIC => 'PASSWORD_FAILED_NUMERIC',
			self::PASSWORD_SPECIAL =>'PASSWORD_FAILED_SPECIAL_CHARACTER',
			self::PASSWORD_TOO_SHORT => 'PASSWORD_TOO_SHORT',
	];
	
	/**
	 * user titles options
	 * @var array
	 */
	public static $titles = [
			'Mr',
			'Mrs',
			'Miss',
			'Ms',
			'Mx',
			'Dr',
			'Prof',
	];
	
	/**
	 * @return string
	 */
	public function getObjectIcon()
	{
		return $this->objectIcon;
	}
	
	/**
	 * return the related translation string for this users authentication type
	 * @return string
     *
     * @throws Exception
	 */
	public function getAuthenticationTypeString()
	{
		if (!isset(self::$authenticationTypes[$this->getAuthenticationType()]))
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: Invalid authentication type for user: ' . $this->getPrimaryKey());
	
			return self::$authenticationTypes[$this->getAuthenticationType()];
	}
	
	/**
	 * Return an object contaning User attributes for authentication
	 * @return stdClass
	 */
	public function getSessionIdentity()
	{
		$identity = new stdClass();
		$identity->id = $this->getPrimaryKey();
		$identity->email = $this->getEmail();
		$identity->systemAdministrator = $this->getSystemAdministrator();
		return $identity;
	}
	
	/**
	 * return a cconcatenated string of title firstName and lastName for this user
	 * @return string
	 */
	public function getFullName()
	{
		return $this->getTitle() . ' ' .$this->getFirstName()  . ' ' . $this->getLastName();
	}
	
	/**
	 * return a User object relating to an AuthenticationService identity
	 * @param StdClass $identity  object containing identity information
	 * @return User|null
	 */
	public static function getUserFromIdentity(StdClass $identity)
	{
		if (!isset($identity->id) || $identity->id == null)
			return null;
	
			$user = UserQuery::create()->findPk((int)$identity->id);
	
			return $user;
	}
	
	/**
	 * return the currently logged in User object
	 * @throws Exception
	 */
	public static function getLoggedIn()
	{
		$auth = new AuthenticationService();
		if(!$auth->hasIdentity())
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: Authentication Service cannot find a logged in user');
	
	
			$identity = $auth->getIdentity();
			$user = UserQuery::create()->findPk((int)$identity->id);
	
			if (is_null($user))
				throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: Authentication Service cannot find a logged in user');
	
				return $user;
	
	}
	
	/**
	 * return an array of select options for user titles
	 * @return array
	 */
	public static function getTitleSelectOptions()
	{
		$options = [];
		foreach(self::$titles as $key => $title)
		{
			$options[$title] = $title;
		}
		return $options;
	}
	
	/**
	 * determine if a password string matches constraints
	 * returns array `message` on failure or boolean `true` on succes
	 * @param string $password
     *
	 * @return bool|array
     * @throws Exception
	 */
	public static function checkPasswordConstraints($password)
	{
		if (is_null($password))
			throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: supplied password cannot be null');
	
			$messages = [];
			if (strlen($password) < self::MIN_PASSWORD_LENGTH)
				$messages[] = self::$passwordErrors[self::PASSWORD_TOO_SHORT];
	
				$passwordArray = str_split($password);
				foreach(self::$passwordComponents as $component => $values)
				{
					$meetsConstraint = false;
					foreach($passwordArray as $character)
					{
						if (strpos($values, $character) !== false)
						{
							$meetsConstraint = true;
						}
					}
						
					if (!$meetsConstraint)
						$messages[] = self::$passwordErrors[$component];
							
				}
	
				if (!empty($messages))
					return $messages;
	
					return true;
	}
	
	/**
	 * create a bcrypt hash of a password string
	 * @param string $password
	 * @return string
	 */
	public static function createPasswordHash($password)
	{
		$crypt = new Bcrypt();
		$passwordHash = $crypt->create($password);
	
		return $passwordHash;
	}
	
	/**
	 * @param InputFilterInterface $inputFilter
	 * @return void|InputFilterAwareInterface
	 * @throws \Exception
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception(__METHOD__.' - Not Implemented');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend\InputFilter.InputFilterAwareInterface::getInputFilter()
	 */
	public function getInputFilter()
	{
		if($this->inputFilter instanceof InputFilter)
			return $this->inputFilter;
	
			$inputFilter = new InputFilter();
	
			$inputFilter->add(array(
					'name' => '_role_id',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'Digits',
							),
					)
			));
	
			$inputFilter->add(array(
					'name' => 'title',
					'required' => false,
					'validators' => array(
							array(
									'name' => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 25
									),
							)
					),
					'filters' => array(
							array(
									'name' => 'Null',
									'options' => array(
											'type' => 'string'
									),
							)
					),
			));
	
			$inputFilter->add(array(
					'name' => 'firstName',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 255
									),
							)
					),
			));
	
			$inputFilter->add(array(
					'name' => 'lastName',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 255
									),
							)
					),
			));
	
			$inputFilter->add(array(
					'name' => 'email',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 500
									)
							),
							array(
									'name' => 'EmailAddress',
							),
					),
					'filters' => array(
							array(
									'name' => 'Null',
									'options' => array(
											'type' => 'string'
									),
							)
					),
			));
	
			$inputFilter->add(array(
					'name' => 'username',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 100
									),
							)
					),
					'filters' => array(
							array(
									'name' => 'Null',
									'options' => array(
											'type' => 'string'
									),
							)
					),
			));
	
			$inputFilter->add(array(
					'name' => 'authenticationType',
					'required' => true,
					'validators' => array(
							array(
									'name' => 'Digits',
							)
					)
			));
	
			return $this->inputFilter = $inputFilter;
	}

    /**
     * return additional requirements for this User as an StdClass object
     * @return StdClass|mixed
     */
    public function getUserDataObject()
    {
        $data = $this->getUserData();
        if (self::isJson($data))
        {
            return json_decode($data);
        } else {
            return new StdClass();
        }
    }

    /**
     * @param StdClass $object
     * @return $this
     */
    public function setUserDataJSON($object)
    {
        $this->setuserData(json_encode($object));
        return $this;
    }

    /**
     * set a given value on a given key in the userData object
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function setDataValue($key, $value)
    {
        $object = $this->getUserDataObject();
        $object->$key = $value;
        $this->setUserDataJSON($object);

        return $this;
    }

    /**
     * return a given value from additional requirements
     * @param $value
     * @return null
     */
    public function getDataValue($key)
    {
        $object = $this->getUserDataObject();
        if (isset($object->$key))
            return $object->$key;

        return null;
    }



}
