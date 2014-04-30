<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Default route
 */
Route::set('orm-api', 'api/<model>(/<id>(/<action>))', array(
    'model' => '.+',
    'id' => '.+',
    'action' => '.+'
))->defaults(array(
    'controller' => 'Api',
));
