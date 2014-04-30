<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Controller that handle api calls.
 *
 * Models can be filtered, ordered, grouped, etc.. using the query. 
 *
 *     'where' => array(
 *          array(<column>, <operator>, <operand>)
 *      ),
 *      'order_by' => array(
 *          array(<column>, <direction>)
 *      ),
 *      'group_by' => array(
 *          array(<column>),
 *          array(<column>)
 *      ),
 *      'having' => array(
 *          array(<column>, <operator>, <operand>)
 *      )
 *
 * And then use your favorite url query encoding tool.
 * 
 * Pluralized models are autodetected using Inflector:
 * 
 *     group is singular
 *     groups is plural
 * 
 * Methods are pluralized by appending _all. For now, only find_all exists, but
 * you may specify your own group operation.
 * 
 * @package   ORM/Api
 * @category  Controllers
 * @author    Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>, Nicolas Hurtubise <316k@legtux.org>
 * @copyright (c) 2013, Hète.ca Inc.
 */
class Controller_Api extends Controller {

    /**
     * Maps HTTP method to model's function.
     */
    public static $methods = array(

        Request::GET    => 'find',
        Request::POST   => 'create',
        Request::PUT    => 'update',
        Request::DELETE => 'delete'
    );

    /**
     * Model to work on.
     * 
     * @var ORM
     */
    protected $model;

    /**
     * Tells wether we are working on a set of model or a single model.
     */
    protected $singular;

    /**
     * Method being called on the model.
     *
     * @var string
     */
    protected $method;

    /**
     * Policy for the model and method.
     *
     * @var Configuration
     */
    protected $policy;

    public function before() {

        $model = Inflector::singular($this->request->param('model'));

        if (!class_exists('Model_' . ucfirst($model))) {

            throw new HTTP_Exception_404(':model not found.', array(':model' => $model));
        }

        // load the model
        $this->model = ORM::factory($model, $this->request->param('id'));
    
        // check if we are dealing on multiple model
        $this->singular = $this->request->param('model') === $model->object_name();

        // index capture the GET, PUT, POST and DELETE, otherwise its a specific model call like count_all or add.
        $this->method = $this->request->action() === 'index' ? Controller_Api::$methods[$this->request->method()] : $this->request->action();
    
        // rename method for plural operation (eg. find_all, count_all)
        if ($this->singular === FALSE) {
            
            $this->method .= '_all';    
        }

        // load the policy for this model
        $this->policy = Kohana::$config->load('policy.' $this->model->object_name() . '.' . $this->request->method());

        // ensure a policy is defined for the given method
        if ($this->policy === NULL) {
            
            // throw a bad method
            throw new HTTP_Exception_405(':model does not allow :method call through api.', array(
                ':model' => $model->object_name(),
                ':method' => $method
            ));
        }

        // apply the query
        foreach ($this->request->query() as $function => $case) {

            // the function must be exposed
            if (!in_array($function, $this->policy->get('functions'))) {

                throw HTTP_Exception_403(':model does not allow :function call through api.', array(
                    ':model' => $model->object_name(),
                    ':function' => $function
                ));
            }

            foreach ($case as $arguments) {
    
                // the column must be exposed
                if (!in_array(Arr::get($arguments, 0), $this->policy->get('columns'))) {

                    throw HTTP_Exception_403(':model does not expose :column through api.', array(
                        ':model' => $model->object_name(),
                        ':column' => $column
                    ));
                }

                call_user_func_array(array($this->model, $function), $arguments)
            }
        }
    }

    public function action_index() {

        // load an unloaded model since update and delete require a loaded model.
        if (!$this->model->loaded() AND in_array($this->method, array('update', 'delete'))) {
            
            $this->model->find();    
        }

        try {
        
            $result = array();

            if ($this->singular) {

                // set values based on expected values from policy
                $this->model->values(json_decode($this->request->post()), $this->policy->get('expected'));
                
                // Make the api call
                $this->model->{$this->method}();
                
                // Filter result columns
                foreach ($columns as $column) {

                    $result[$column] = $this->model->{$column};
                }
            
            } else {

                // Make the plural api call
                $this->model->{$method . '_all'}();
                
                foreach ($this->model->{$method}() as $model) {
                
                    // Filter result columns
                    foreach ($columns as $column) {

                        $result[][$column] = $this->model->{$column};
                    }
                }
                
            }

            $this->response
                    ->headers('Content-Type', 'application/json; charset=utf-8')
                    ->body(json_encode($result, JSON_UNESCAPED_UNICODE));
    
        } catch (ORM_Validation_Exception $ove) {

            /**
             * Dump the error in json.
             */
            $this->response
                    ->headers('Content-Type', 'application/json; charset=utf-8')
                    ->body(json_encode($ove->errors('model'), JSON_UNESCAPED_UNICODE))
                    ->status(401);
        }
    }
    
    /**
     * Count models matching the query.
     *
     * <model> is assumed to be plural.
     */
    public function action_count() {
        
        $this->response
                ->headers('Content-Type', 'application/json; charset=utf-8')
                ->body(json_encode($this->model->count_all(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * Add a relationship
     *
     *    {
     *        'alias': <alias>
     *        'far_keys': <far_keys>
     *    }
     */
    public function action_add() {
        
        $add = json_decode($this->request->body());

        $validation = Validation::factory($add)
            ->rule('alias', 'not_empty')
            ->rule('alias', 'in_array', array(':value', $this->policy->get('columns')));

        if (!$validation->check()) {

            throw new HTTP_Exception_403('alias and far_keys must be specified.');
        }

        $this->response
            ->headers('Content-Type', 'application/json; charset=utf-8')    
            ->body(json_encode($this->model->add($has['alias'], Arr::get($has, 'far_keys')), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Remove a relationship
     *
     *    {
     *        'alias': <alias>
     *        'far_keys': <far_keys>
     *    }
     */
    public function action_remove() {

        $remove = json_decode($this->request->body());

        $validation = Validation::factory($remove)
            ->rule('alias', 'not_empty')
            ->rule('alias', 'in_array', array(':value', $this->policy->get('columns')));

        if (!$validation->check()) {

            throw new HTTP_Exception_403('alias and far_keys must be specified.');
        }

        $this->response
            ->headers('Content-Type', 'application/json; charset=utf-8')    
            ->body(json_encode($this->model->remove($remove['alias'], Arr::get($remove, 'far_keys')), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Check if a relationship exists.
     *
     *    {
     *        'alias': <alias>
     *        'far_keys': <far_keys>
     *    }
     */
    public function action_has() {

        $has = json_decode($this->request->body());

        $validation = Validation::factory($has)
            ->rule('alias', 'not_empty')
            ->rule('alias', 'in_array', array(':value', $this->policy->get('columns')));

        if (!$validation->check()) {

            throw new HTTP_Exception_403('alias and far_keys must be specified.');
        }
        
        $this->response
            ->headers('Content-Type', 'application/json; charset=utf-8')    
            ->body(json_encode($this->model->has($has['alias'], Arr::get($has, 'far_keys')), JSON_UNESCAPED_UNICODE));
    }
}
