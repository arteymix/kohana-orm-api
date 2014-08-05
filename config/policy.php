<?php
defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => array(
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
	)
);
