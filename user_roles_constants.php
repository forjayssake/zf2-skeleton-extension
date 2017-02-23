<?php
/**
 * This file contains global constant definitions for all user roles. These constants can be 
 * 	called directly in code in any module of the application
 */
define('GUEST', 1);


/**
 * This constant defines user roles that are able to see the setup menu if included in your application
 * Any number of roles can be added to the SETUP_ROLES array, system administrators can see the menu by default
 *  regardless of their role
 * The json_encode syntax is used to accommodate PHP < 5.6. PHP 5.6+ allows the creation of arrays as constants. The
 *  view helper used to render the setup menu will work with either a string or an array
 */
define ("SETUP_ROLES", json_encode([1]));