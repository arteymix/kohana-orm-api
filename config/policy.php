<?php

/**
 * Policy for authorized calls on model through api.
 */
return array(
    'User' => array(
        'find' => array(
            'columns' => array(), // exposed columns in the query
            'functions' => array('where', 'order_by') // exposed functions in the call
        ),
        'update' => array(
            'columns' => array(), // columns through which search can be done
            'expected' => array() // columns updated following a POST request
        )
    )
);
