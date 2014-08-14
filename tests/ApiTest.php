<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Model used for tests
 */
class Model_User extends Model_Auth_User {
	
}

/**
 * Tests
 *
 * A testing database must be set with the basic ORM auth structure.
 *
 * Do not run these tests on real data.
 *
 * @package orm-api
 * @category Tests
 * @author Guillaume Poirier-Morency <guillaumepoiriermorency@gmail.com>
 * @license BSD-3-Clauses
 */
class ApiTest extends Unittest_TestCase {

	/**
	 * Mock a User model for testing
	 */
	public function getUser()
	{
		return ORM::factory('User')
						->values(array(
							'username' => 'test',
							'email' => 'test@example.com',
							'password' => 'abcd1234'))
						->create();
	}

	public function testFind()
	{
		$user = $this->getUser();

		$response = Request::factory('api/user/' . $user)
				->method(Request::GET)
				->execute();

		$this->assertEquals(200, $response->status());
	}

	/**
	 * This also test plural calls.
	 */
	public function testFindAll()
	{
		$model = $this->getUser();

		$response = Request::factory('api/users')
				->method(Request::GET)
				->execute();

		$this->assertEquals(200, $response->status());
	}

	public function testCreate()
	{
		$response = Request::factory('api/user')
				->method(Request::PUT)
				->execute();

		$this->assertEquals(200, $response->status());
		$this->assertEquals('application/json', $response->headers('Content-Type'));
	}

	public function testCreateLoaded()
	{
		$user = $this->getUser();
		$response = Request::factory('api/user/' . $user)
				->method(Request::PUT)
				->execute();

		$this->assertEquals(403, $response->status());
		$this->assertEquals('application/json', $response->headers('Content-Type'));
		$this->assertEquals('', json_decode($response->body()));
	}

	public function testUpdate()
	{
		$model = $this->getUser();

		$response = Request::factory('api/user/' . $model)
				->method(Request::POST)
				->execute();

		$this->assertEquals(200, $response->status());
		$this->assertEquals('application/json', $response->headers('Content-Type'));
	}

	public function testDelete()
	{
		$model = $this->getUser();

		$response = Request::factory('api/user/' . $model)
				->method(Request::DELETE)
				->execute();

		$model->reload();

		$this->assertFalse($model->loaded());

		$this->assertEquals(200, $response->status());
		$this->assertEquals('application/json', $response->headers('Content-Type'));
	}

	public function testDeleteUnloaded()
	{
		$response = Request::factory('api/user/123123')
				->method(Request::DELETE)
				->execute();

		$this->assertEquals(403, $response->status());
		$this->assertEquals('application/json', $response->headers('Content-Type'));
		$this->assertEquals('', json_decode($response->body()));
	}

	public function tearDown()
	{
		DB::delete('users')->execute();

		parent::tearDown();
	}

}
