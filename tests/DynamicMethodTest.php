<?php

namespace se\DynObject\Test;


use se\DynObject\DynamicObject;
use se\DynObject\DynamicMethod;

class DynamicMethodTestCase extends \PHPUnit_Framework_TestCase
{
	public function testNew()
	{
		$new = $this->getDynamicObject();
		$method = $new->method('echo');
		$this->assertInstanceOf('se\DynObject\DynamicMethod', $method);
		$this->assertEquals($method->getObject(), $new);
	}

	public function testImplementations()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		
		$method = $obj
		->method('echo')
		->implementation(function(DynamicObject $object, $arg1) use ($test, $obj){
			$test->assertEquals('HELLO', $arg1);
			$test->assertSame($obj, $object);
			return strtolower($arg1);
		}, 'lowercase', true)
		->implementation(function(DynamicObject $object, $arg1) use ($test, $obj){
			$test->assertEquals('hello', $arg1);
			$test->assertSame($obj, $object);
			return strtoupper($arg1);
		}, 'uppercase')
		
		;
		
		$this->assertEquals($method->getCurrentImplementation(), 'lowercase');
		$result = $method->call('HELLO')->result();
		$this->assertEquals('hello', $result);
		
		$method->setCurrentImplementation('uppercase');
		$this->assertEquals($method->getCurrentImplementation(), 'uppercase');
		$result = $method->call('hello')->result();
		$this->assertEquals('HELLO', $result);
		
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
