<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * GET:
 *     Used to store search criteria
 *     id: 1
 *     id: {'>':1} // With operators
 * 
 * POST:
 *     Used to post values for filling the ORM
 * 
 * Methods are pluralized by appending _all. Operating on a plural model will
 * do group operation.
 * 
 * @package orm-api
 * @category Controller
 * @author Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Controller_Api extends Controller {

    /**
     * Model to work on.
     * 
     * @var ORM
     */
    protected $model;

    /**
     *
     * @var variant 
     */
    protected $body;   

    public function after() {

        $this->response
                ->headers('Content-Type', 'application/json')
                ->body(json_encode($this->body, JSON_UNESCAPED_UNICODE));

        parent::after();
    }

    public function action_index() {

        $model = $this->request->param('model');
        $singular = Inflector::singular($model) === $model;

        $this->model = ORM::factory(Inflector::singular($model));

        foreach ($this->request->query() as $column => $criteria) {
            if (Arr::is_array($criteria)) {
                foreach ($criteria as $operator => $value) {
                    $this->model->where($column, $operator, $value);
                }
            } else {
                $this->model->where($column, '=', $criteria);
            }
        }

        // Pluralize method with _all
        $method = $singular ? $this->request->param('method') : $this->request->param('method') . '_all';

        $validation = Validation::factory($this->request->param())
                ->rule('method', 'in_array', array($method, $this->model->api_methods()));

        try {

            if (!$validation->check($validation)) {
                throw new ORM_Validation_Exception('', $validation);
            }

            // Make the api call
            $this->body = $this->model->{$method}()->as_array(NULL, $this->model->primary_key());
            
        } catch (ORM_Validation_Exception $ove) {
            $this->body = $ove->errors('model');
            $this->response->status(401);
        }
    }
}

?>
