<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Interface implemented by models which are supported through api.
 * 
 * @package Api
 * @author Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 * @copyright (c) 2013, Hète.ca Inc.
 */
interface ORM_Api {

    /**
     * List of visible columns.
     */
    public function api_columns();

    /**
     * List of expected columns.
     */
    public function api_expected();

    /**
     * List of supported method to call on the model.
     */
    public function api_methods();
}

?>
