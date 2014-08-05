<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * Policy for authorized calls on model through api.
 */
return array(
	'User' => array(
		'find' => array(
			// exposed columns on find
			'email'
		), 
		'update' => array(
			// exposed columns on update
			'username', 'password'
		)
	)
);
