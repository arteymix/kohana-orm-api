<?php

/**
 * Tests
 *
 * A testing database must be set with the basic ORM auth structure.
 *
 * @package orm-api
 * @author  Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 */
class ApiTest extends Unittest_TestCase {
    
    /**
     * Mock an object for testing
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
    }

    public function testCreate() {}
    
    public function testUpdate() {}
    
    public function testDelete() {}    
}
