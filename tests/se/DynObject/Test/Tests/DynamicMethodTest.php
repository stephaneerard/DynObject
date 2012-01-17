<?php

namespace se\DynObject\Test\Tests;


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

	/**
	 * @expectedException se\DynObject\Exceptions\MethodNotFoundException
	 */
	public function testCallingInexistantMethod()
	{
		$this->getDynamicObject()->call('hello');
	}

	/**
	 * @expectedException se\DynObject\Exceptions\MethodImplementationNotFoundException
	 */
	public function testCallingInexistantMethodImpl()
	{
		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function(){
		}, 'default', true)
		->implementation(function(){
		}, 'non-default')
			
		->setCurrentImplementation('inexistant')
		->call('test')
		;
	}

	public function testMethodNotFoundHandler()
	{
		$passedInHandler = false;

		$object = $this->getDynamicObject();
		$object
		->setMethodNotFoundHandler(function()use(&$passedInHandler){
			$passedInHandler = true;
		});

		$object->call('inexistant');

		$this->assertTrue($passedInHandler);
	}

	public function testGetReflection()
	{
		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function(){
		}, 'default', true)
		;

		$ref = $method->getReflection();
		$this->assertInstanceOf('\ReflectionFunction', $ref);
	}

	public function testDirectInvocation()
	{
		$passedInLambda = false;
		$arg1AndArg2AsExpected = false;

		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function($object, $arg1, $arg2)use(&$passedInLambda, &$arg1AndArg2AsExpected){
			$passedInLambda = true;
			$arg1AndArg2AsExpected = ($arg1 == 'arg1' && $arg2 == 'arg2');
		}, 'default', true)
		;

		$method('arg1', 'arg2');

		$this->assertTrue($passedInLambda);
		$this->assertTrue($arg1AndArg2AsExpected);
	}

	public function testMethodReturn()
	{
		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function($object, $arg1, $arg2)use(&$passedInLambda, &$arg1AndArg2AsExpected){
			return $arg1 . $arg2;
		}, 'default', true)
		;

		$result = $method('arg1', 'arg2')->result();

		$this->assertEquals('arg1arg2', $result);
	}

	public function testCallingGivingArgsDirectly()
	{
		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function($object, $arg1, $arg2)use(&$passedInLambda, &$arg1AndArg2AsExpected){
			return $arg1 . $arg2;
		}, 'default', true)
		;

		$result = $method->call('arg1', 'arg2')->result();

		$this->assertEquals('arg1arg2', $result);
	}

	public function testListenerBeforeCall()
	{
		$passedInListener = false;

		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function($object, $arg1, $arg2)use(&$passedInLambda, &$arg1AndArg2AsExpected){
			return $arg1 . $arg2;
		}, 'default', true)
		->listener('before', 'call', function()use(&$passedInListener){
			$passedInListener = true;
		}, 'test')
		;

		$result = $object->call('test', array('arg1', 'arg2'));

		$this->assertTrue($passedInListener);
		$this->assertEquals('arg1arg2', $result);
	}

	public function testListenerBeforeCallAndRemovingListener()
	{
		$passedInListener = false;

		$object = $this->getDynamicObject();

		$method = $object
		->method('test')
		->implementation(function($object, $arg1, $arg2)use(&$passedInLambda, &$arg1AndArg2AsExpected){
			return $arg1 . $arg2;
		}, 'default', true)
		->listener('before', 'call', function()use(&$passedInListener){
			$passedInListener = true;
		}, 'test')
		->removeListener('before', 'call', 'test')
		;

		$result = $object->call('test', array('arg1', 'arg2'));

		$this->assertFalse($passedInListener);
		$this->assertEquals('arg1arg2', $result);
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
