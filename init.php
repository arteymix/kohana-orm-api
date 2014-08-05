<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * Route matching api calls.
 */
Route::set('orm-api', 'api/<model>(/<id>(/<action>))', array(
        'model' => '.+', 
        'id' => '.+', 
        'action' => '.+'
))->defaults(array(
	'controller' => 'Api'
));
