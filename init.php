<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Default route
 */
Route::set('orm-api', 'api/<model>(/<action>)(/<id>)', array(
    'id' => '.+',
    'action' => 'count',
))->defaults(array(
    'controller' => 'api',
    'action' => 'index',
));

?>
