<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Route for api
 */
Route::set('api', 'api/<model>(/<method>)', array(
    'method' => 'create|update|save',
))->defaults(array(
    'controller' => 'api',
    'action' => 'index',
    'method' => 'find'
));
?>
