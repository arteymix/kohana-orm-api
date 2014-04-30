<?php

/**
 * Policy for authorized calls on model through api.
 */
return array(
    'User' => array(
        'find' => array(
            'exposed' => array(), // exposed columns in the query
        ),
        'update' => array(
            'exposed' => array(), // columns through which search can be done
            'expected' => array() // columns updated following a POST request
        )
    )
);
