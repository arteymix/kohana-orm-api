<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Tests
 *
 * A testing database must be set with the basic ORM auth structure.
 *
 * @package  orm-api
 * @category Tests
 * @author   Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 * @license  BSD 3-clauses
 */
class ApiTest extends Unittest_TestCase {
    
    /**
     * Mock a User model for testing
     */
    public function getUser() {

        return ORM::factory('User')
            ->values(array(
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => 'abcd1234'
            ))  
            ->create();
    }
    
    public function testFind() {
        
        $model = $this->getModel();    

        $response = Request::factory('api/user/' . $model)
            ->method(Request::GET)
            ->execute();

        $this->assertEquals(200, $response->status());
    }

    public function testCreate() {
        
        $response = Request::factory('api/user')
            ->method(Request::PUT)
            ->execute();

        $this->assertEquals(200, $response->status());
    }

    public function testCreateLoaded() {
        
        $response = Request::factory('api/user/123123')
            ->method(Request::PUT)
            ->execute();

        $this->assertEquals(403, $response->status());
        $this->assertEquals('', json_decode($response->body()));
    }
    
    public function testUpdate() {
        
        $model = $this->getModel();    

        $response = Request::factory('api/user/' . $model)
            ->method(Request::POST)
            ->execute();

        $this->assertEquals(200, $response->status());
    }
    
    public function testDelete() {
        
        $model = $this->getModel();    

        $response = Request::factory('api/user/' . $model)
            ->method(Request::DELETE)
            ->execute();

        $this->assertEquals(200, $response->status());
    }    

    public function testDeleteUnloaded() {
        
        $response = Request::factory('api/user/123123')
            ->method(Request::DELETE)
            ->execute();

        $this->assertEquals(403, $response->status());
        $this->assertEquals('', json_decode($response->body()));
    }

    public function tearDown() {
        
        DB::delete('users')->execute();

        parent::tearDown();    
    }
}
