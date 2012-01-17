<?php

namespace se\DynObject\Test\Tests;


use se\DynObject\DynamicObject;
use se\DynObject\DynamicMethod;
use se\DynObject\DynamicProperty;

class DynamicObjectTestCase extends \PHPUnit_Framework_TestCase
{

	public function testHasMethodWhenHasNot()
	{
		$object = $this->getDynamicObject();

		$has = $object->hasMethod('inexistant');
		$this->assertFalse($has);
	}

	public function testHasMethodWhenHas()
	{
		$object = $this->getDynamicObject();

		$object->method('exists');

		$has = $object->hasMethod('exists');
		$this->assertTrue($has);
	}

	public function testHasPropertyWhenHasNot()
	{
		$object = $this->getDynamicObject();

		$has = $object->hasProperty('inexistant');
		$this->assertFalse($has);
	}

	public function testHasPropertyWhenHas()
	{
		$object = $this->getDynamicObject();

		$object->property('exists');

		$has = $object->hasProperty('exists');
		$this->assertTrue($has);
	}

	public function testPassingFeatureInstanceWhenDefining()
	{
		$object = $this->getDynamicObject();
		$method = $object->method('test');

		$object = $this->getDynamicObject();
		$object->method('test', $method);

		$this->assertSame($method, $object->method('test'));
	}

	public function testCallReturnsResultUsingImpl()
	{
		$object = $this->getDynamicObject();
		$method = $object->method('test')
		->implementation(function($object, $arg1, $arg2){
			return array();
		}, 'default', true)
		->implementation(function($object, $arg1, $arg2){
			return array($arg1, $arg2, $object);
		}, 'other');

		$result = $object->call('test', array('arg1', 'arg2', $object), true, 'other');

		$this->assertEquals(array('arg1', 'arg2', $object), $result);
	}


	public function testCallReturnsThis()
	{
		$object = $this->getDynamicObject();
		$method = $object->method('test')
		->implementation(function($object, $arg1, $arg2){
			return array();
		}, 'default', true)
		->implementation(function($object, $arg1, $arg2){
			return array($arg1, $arg2, $object);
		}, 'other');

		$result = $object->call('test', array('arg1', 'arg2', $object), false, 'other');

		$this->assertEquals($object, $object);
	}

	/**************************
	 *
	* 		HELPER METHODS
	*
	*************************/



	/**
	 * @param string $class
	 * @return DynamicObject
	 */
	protected function getDynamicObject($class = null)
	{
		return DynamicObject::instanciate($class);
	}
}
