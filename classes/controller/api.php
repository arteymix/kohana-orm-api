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
 * @author Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>, Nicolas Hurtubise <316k@legtux.org>
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Controller_Api extends Controller {

    public static $methods = array(
        Request::GET => "find",
        Request::POST => "create",
        Request::PUT => "update",
        Request::DELETE => "delete"
    );

    /**
     * Model to work on.
     * 
     * @var ORM
     */
    protected $model;
    
    public function before() {
        $model = $this->request->param('model');
        
        $this->model = ORM::factory(Inflector::singular($model));

        if (!$this->model instanceof ORM_Api) {
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => Inflector::singular($model)));
        }
    }

    public function action_index() {
        $model = $this->request->param('model');
    
        $singular = Inflector::singular($this->request->param('model')) === $model;

        if (!class_exists('Model_' . ucfirst(Inflector::singular($model)))) {
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => Inflector::singular($model)));
        }

        $columns = $this->model->api_columns();

        if ($columns === NULL) {
            $columns = array_keys($this->model->table_columns());
        }
        
        if(($id = $this->request->param('id')) !== NULL) {
           $this->model->where($this->model->primary_key(), '=', $id); 
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
        
        $method = Controller_Api::$methods[$this->request->method()] . ($singular ? '' : '_all');
        
        $validation = Validation::factory(array('method' => $method))
                ->rule('method', 'in_array', array($method, $this->model->api_methods()));

        if (!$validation->check($validation)) {
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => Inflector::singular($model)));
        }
        
        try {
        
            $result = array();
            
            if(in_array($method, array('update', 'delete'))) {
                $this->model->find();
            }

            if ($singular) {

                if(in_array($method, array('update', 'create'))) {
                    if($method == 'update') {
                        $values = $this->request->post();
                    }
                    else if($method == 'create') {
                        parse_str($this->request->body(), $values);
                    }
                    
                    foreach($values as $column => $value) {
                        if(in_array($column, $columns)) {
                            $this->model->{$column} = $value;
                        }
                    }
                }
                
                // Make the api call
                $_result = $this->model->{$method}()->as_array();
                
                // Filter result columns
                foreach ($columns as $column) {
                    $result[$column] = $_result[$column];
                }
            
            } else {
                // Make the api call
                
                foreach($this->model->{$method}() as $index => $model) {
                
                    $_result = $model->as_array();
                    
                    // Filter result columns
                    foreach ($columns as $column) {
                        $result[$index][$column] = $_result[$column];
                    }
                    
                }
                
            }

            $this->response
                    ->headers('Content-Type', 'application/json; charset=utf-8')
                    ->body(json_encode($result, JSON_UNESCAPED_UNICODE));
    
        } catch (ORM_Validation_Exception $ove) {
            $this->response
                    ->headers('Content-Type', 'application/json; charset=utf-8')
                    ->body(json_encode($ove->errors('model'), JSON_UNESCAPED_UNICODE))
                    ->status(401);
        }
    }
    
    public function action_count() {
        $model = Inflector::singular($this->request->param('model'));
    
        if(!in_array("count_all", $this->model->api_methods())){
            throw new HTTP_Exception_404('Model :model not found.', array(':model' => $model));
        }
        
        $result = array('count' => $this->model->count_all());
        
        $this->response
                    ->headers('Content-Type', 'application/json; charset=utf-8')
                    ->body(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

}

?>
