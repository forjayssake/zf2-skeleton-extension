<?php

use Base\AccessManagementLog as BaseAccessManagementLog;
use AccessManagement\Form\AccessManagementForm;

/**
 * Skeleton subclass for representing a row from the 'accessmanagementlogs' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class AccessManagementLog extends BaseAccessManagementLog
{
    /**
     * @const integer
     */
    const ACCESS_DENIED = 1;

    /**
     * suffix to form element names for role based access config
     *  e.g. `guest_disabledlogin`
     * @const string
     */
    const DISABLED_LOGIN_ELEMENT_SUFFIX = '_disablelogin';

    /**
     * suffix to form element names for role based access config
     *  e.g. `guest_message`
     * @const string
     */
    const MESSAGE_ELEMENT_SUFFIX = '_message';

    /**
     * suffix to form element names for role based access config
     *  e.g. `guest_messagetype`
     * @const string
     */
    const MESSAGE_TYPE_ELEMENT_SUFFIX = '_messagetype';

    /**
     * suffix to form element names for role based access config
     *  e.g. `guest_datetimeFrom`
     * @const string
     */
    const DATETIME_FROM_ELEMENT_SUFFIX = '_datetimeFrom';

    /**
     * suffix to form element names for role based access config
     *  e.g. `guest_datetimeTo`
     * @const string
     */
    const DATETIME_TO_ELEMENT_SUFFIX = '_datetimeTo';

    /**
     * Icon to prefix on informational access messages
     * @const string
     */
    const INFO_MESSAGE_ICON     = 'fa-info-circle';

    /**
     * Icon to prefix on warning access messages
     * @const string
     */
    const WARNING_MESSAGE_ICON  = 'fa-warning';

    /**
     * Icon to prefix on error access messages
     * @const string
     */
    const ERROR_MESSAGE_ICON   = 'fa-exclamation-circle';

    /**
     * Session container namespace for role specific access config
     * @const string
     */
    const ROLE_MESSAGE_NAMESPACE = 'roles_access_management_messages';

    /**
     * Session container namespace for all user access config
     * @const string
     */
    const ALL_USER_MESSAGE_NAMESPACE = 'all_users_access_management_messages';

    /**
     * fetch the most current access managments log
     * @return AccessManagementLog|null
     */
    public static function fetchCurrentLog()
    {
        return AccessManagementLogQuery::create()->orderById('DESC')->findOne();
    }

    /**
     * fetch the current access management config
     * @param bool $assoc return config as an associative array
     * @return mixed|null
     */
    public static function fetchCurrentConfig($assoc = false)
    {
        $log = self::fetchCurrentLog();

        if (is_null($log))
            return null;

        $config = $log->getConfig();

        if (is_null($config))
            return  null;

        return json_decode($config, $assoc);
    }

    /**
     * create JSON config record and save to new row
     * @param AccessManagementForm $form
     * @return void
     */
    public static function populateConfig(AccessManagementForm $form)
    {
        $accessConfig = [];
        foreach($form->fetchLogElements() as $key => $elementName)
        {
            $element = $form->get($elementName);
            if (!is_null($element))
            {
                $accessConfig[$element->getName()] = self::stripScript($element->getValue());
            }
        }

        $log = new AccessManagementLog();
        $log->set_user_id(User::getLoggedIn()->getPrimaryKey());
        $log->setconfig(json_encode($accessConfig));
        $log->save();
    }


    /**
     * determine whether a given User can access the application from the current config
     * @param User $user
     * @return bool
     */
    public static function fetchAccessForUser(User $user)
    {
        if ($user->getsystemAdministrator())
            return true;

        $config = self::fetchCurrentConfig();

        // are all user types restricted from accessing the application?
        $allRoles_disabled = 'allroles' . self::DISABLED_LOGIN_ELEMENT_SUFFIX;
        if (isset($config->$allRoles_disabled) && $config->$allRoles_disabled == self::ACCESS_DENIED)
        {
            // check date boundaries if set
            $dates = self::fetchConfigDatesForRoles($config, 'allroles');
            if (!is_null($dates))
            {
                return self::isInDateRange($dates) ? false : true;
            }


            return false;
        }

        // is a the given user role restricted from accessing the application?
        $role = $user->getRole();
        $accessName = strtolower($role->getconstant() . self::DISABLED_LOGIN_ELEMENT_SUFFIX);
        if (isset($config->$accessName) && $config->$accessName == self::ACCESS_DENIED)
        {
            // check date boundaries if set
            $dates = self::fetchConfigDatesForRoles($config, $role->getconstant());
            if (!is_null($dates))
            {
                return self::isInDateRange($dates) ? false : true;
            }

            return false;
        }

        return true;
    }

    /**
     * determine whether now falls inside a given date range
     * @param array $dates
     *
     * @return bool
     */
    private static function isInDateRange(array $dates = [])
    {
        $now = new DateTime();
        if ($dates['from'] !== false && $dates['to'] !== false)
        {
            if ($dates['from'] <= $now && $dates['to'] >= $now)
            {
                return true;
            }
        } elseif ($dates['from'] !== false) {
            if ($dates['from'] <= $now)
            {
                return true;
            }
        } elseif($dates['to'] !== false) {
            if ($dates['to'] >= $now)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Return DateTime objects for stored config date strings
     * @param StdClass $config
     * @param string $role
     *
     * @return array | null
     */
    private static function fetchConfigDatesForRoles($config, $role)
    {
        $role           = strtolower($role);
        $roles_dateFrom = $role . self::DATETIME_FROM_ELEMENT_SUFFIX;
        $role_dateTo    = $role . self::DATETIME_TO_ELEMENT_SUFFIX;

        $dateFrom = false;
        $dateTo = false;

        if (isset($config->$roles_dateFrom) && strlen($config->$roles_dateFrom) > 0)
        {
            $dateFrom = DateTime::createFromFormat('Y-m-d H:i', $config->$roles_dateFrom);
        }

        if (isset($config->$role_dateTo) && strlen($config->$role_dateTo) > 0)
        {
            $dateTo = DateTime::createFromFormat('Y-m-d H:i', $config->$role_dateTo);
        }

        if ($dateFrom === false && $dateTo === false)
            return null;

        return ['from' => $dateFrom, 'to' => $dateTo];
    }



    /**
     * return an array of access management messages
     * @param User|null $user
     * @parma bool $roleOnly return role specific messages only
     * @return array
     */
    public static function fetchAccessMessages(User $user = null, $roleOnly = false)
    {
        $messages = [];

        $config = self::fetchCurrentConfig();

        if (!$roleOnly)
        {
            $allRolesMessage = 'allroles' . self::MESSAGE_ELEMENT_SUFFIX;
            $allRoleType = 'allroles' . self::MESSAGE_TYPE_ELEMENT_SUFFIX;
            if (isset($config->$allRolesMessage) && strlen($config->$allRolesMessage) > 0) {
                $messages[$allRolesMessage] = [
                    'type' => str_replace('alert-', '', $config->$allRoleType),
                    'message' => $config->$allRolesMessage
                ];
            }
        }

        if (!is_null($user)) {
            $role = $user->getRole();
            $messageName = strtolower($role->getconstant() . self::MESSAGE_ELEMENT_SUFFIX);
            $messageType = strtolower($role->getconstant() . self::MESSAGE_TYPE_ELEMENT_SUFFIX);
            if (isset($config->$messageName) && strlen($config->$messageName) > 0) {
                $messages[$messageName] = [
                    'type' => str_replace('alert-', '', $config->$messageType),
                    'message' => $config->$messageName
                ];
            }
        }

        return $messages;
    }

    private static function stripScript($inString)
    {
        if (is_string($inString)) {
            $outString = str_replace(['<script', '</script>'], '', $inString);
            return $outString;
        } else {
            return $inString;
        }
    }

}
