<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Controller that handle api calls.
 * 
 * You may only call methods that are specified in ORM_Api::api_methods().
 * 
 * Values in the query are used to filter the search. You may specify the
 * columns to be matched like this:
 * 
 * ?id=123
 * 
 * Or you could also use operators:
 * 
 * id: {
 *     '>': 136,
 *     '<': 145
 * }
 * 
 * And then use your favorite url encoding tool.
 * 
 * You may only seek the model by columns specified in ORM_Api::api_columns().
 * 
 * Post values will be filled in the model. You may only fill values with
 * columns matching ORM_Api::api_expected().
 * 
 * Pluralized models are autodetected using Inflector:
 * 
 * groupe is singular
 * groupes is plural
 * 
 * Methods are pluralized by appending _all. For now, only find_all exists, but
 * you may specify your own group operation.
 * 
 * @package Api
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

    public function action_index() {

        $model = $this->request->param('model');
        $singular = Inflector::singular($model) === $model;

        if (!class_exists('Model_' . ucfirst(Inflector::singular($model)))) {
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => Inflector::singular($model)));
        }

        $this->model = ORM::factory(Inflector::singular($model));

        if (!$this->model instanceof ORM_Api) {
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => Inflector::singular($model)));
        }

        $columns = $this->model->api_columns();

        if ($columns === NULL) {
            $columns = array_keys($this->model->table_columns());
        }

        foreach ($this->request->query() as $column => $criteria) {
            if (in_array($column, $columns)) {
                if (Arr::is_array($criteria)) {
                    foreach ($criteria as $operator => $value) {
                        $this->model->where($column, $operator, $value);
                    }
                } else {
                    $this->model->where($column, '=', $criteria);
                }
            }
        }

        // Pluralize method with _all
        $method = $singular ? $this->request->param('method') : $this->request->param('method') . '_all';

        $validation = Validation::factory($this->request->param())
                ->rule('method', 'in_array', array($method, $this->model->api_methods()));

        if (!$validation->check($validation)) {
            throw new Validation_Exception($validation);
        }

        try {
            switch ($method) {
                case 'update':
                case 'save':
                    // All these calls require a loaded model
                    $this->model->find();
                default:

                    $result = NULL;

                    if ($singular) {

                        // Make the api call
                        $_result = $this->model->{$method}()->as_array();

                        // Filter result columns
                        foreach ($columns as $column) {
                            $result[$column] = $_result[$column];
                        }
                    } else {
                        // Make the api call
                        $result = $this->model->{$method}()->as_array(NULL, $this->model->primary_key());
                    }

                    $this->response
                            ->headers('Content-Type', 'application/json')
                            ->body(json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        } catch (ORM_Validation_Exception $ove) {
            $this->response
                    ->headers('Content-Type', 'application/json')
                    ->body(json_encode($ove->errors('model'), JSON_UNESCAPED_UNICODE))
                    ->status(401);
        }
    }

}

?>
