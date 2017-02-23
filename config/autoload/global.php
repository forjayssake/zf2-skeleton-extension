<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
	'session_config' =>
		[
			'cache_expire'			=> 60*60*2,
			'name'					=> 'sess',
			'cookie_lifetime'		=> 0,
			'gc_maxlifetime'		=> 60*60*2,
			'cookie_path'			=> '/',
			'cookie_httponly'		=> true,
			'remember_me_seconds'	=> 60*60*2,
			'use_cookies'			=> true,
		],
);
