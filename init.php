<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Route for api helpers
 */
Route::set('api_count', 'api/count/<model>', array(
    
))->defaults(array(
    'controller' => 'api',
    'action' => 'count',
));

/**
 * Route for api elementary functions
 */
Route::set('api', 'api/<model>(/<id>)', array(
    'id' => '.+'
))->defaults(array(
    'controller' => 'api',
    'action' => 'index',
));
    
?>
