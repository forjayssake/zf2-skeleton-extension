<?php
namespace Application\Assertion;

use User;

class AssertUserCanViewSetupMenu
{

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var int
     */
    protected $role;

    /**
     * @var array
     */
    protected $validRoles = [];


    public function __construct(User $user, array $config = [])
    {
        $this->user = $user;
        $this->role = $user->get_role_id();
        $this->setConfig($config);
        $this->fetchValidRoles();
    }

    /**
     * populate the $this->config array and check for a valid setup configuration
     * @param array $config
     * @return $this
     * @throws \Exception
     */
    private function setConfig(array $config = [])
    {
        $this->config = $config;

        if (!isset($this->config['navigation']['setup']) && count($this->config['navigation']['setup']) > 0)
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: No setup menu configuration found.');

        return $this;
    }

    /**
     * populate the $this->validRoles array from the user_role_constants config
     * @return $this
     * @throws \Exception
     */
    private function fetchValidRoles()
    {
        if (!defined(SETUP_ROLES))
        {
            $this->validRoles = [];
        } else {
            $roles = SETUP_ROLES;

            if (is_string($roles)) {
                $json = $this->isJson($roles);
                if ($json === false) {
                    throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . ' Says: SETUP_ROLES must be valid json or an array');
                }
                $this->validRoles = $json;
            } else {
                $this->validRoles = $roles;
            }
        }

        return $this;
    }

    /**
     * determine whether a given string contains valid json
     * @param $jsonString
     * @return bool|array
     */
    private function isJson($jsonString)
    {
        $json = json_decode($jsonString, true);
        return (json_last_error() == JSON_ERROR_NONE) ? $json : false;
    }

    /**
     * @return bool
     */
    public function assert()
    {
        if ($this->user->getsystemAdministrator())
            return true;

        if (in_array($this->role, $this->validRoles))
            return true;

        return false;
    }
}