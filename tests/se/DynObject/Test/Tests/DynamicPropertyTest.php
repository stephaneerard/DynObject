<?php

namespace se\DynObject\Test\Tests;

use se\DynObject\DynamicProperty;
use se\DynObject\DynamicObject;
use se\DynObject\Test\Libs\TestClass;

class DynamicPropertyTestCase extends \PHPUnit_Framework_TestCase
{
	public function testNew()
	{
		$new = DynamicObject::instanciate();
		$property = $new->property('name');
		$this->assertInstanceOf('se\DynObject\DynamicProperty', $property);
		$this->assertEquals($property->getObject(), $new);
	}

	public function testPropertyWithGetterAndSetterThenSetAndGet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		;

		$obj->set('name', 'value');
		$result = $obj->get('name');
		$this->assertEquals('value', $result);
	}

	/**
	 *
	 * @expectedException \RuntimeException
	 */
	public function testPropertyWithGetterAndNoSetterThenSet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		;
		$obj->set('name', 'value');
	}

	/**
	 *
	 * @expectedException \RuntimeException
	 */
	public function testPropertyWithNoSetterThenSet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		;
		$obj->set('name', 'value');
	}


	/**
	 *
	 * @expectedException \RuntimeException
	 */
	public function testPropertyWithNoGetterThenGet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		;
		$obj->get('name', 'value');
	}

	/**
	 *
	 */
	public function testPropertyWithSpecificGetterThenGet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->getter(function(DynamicProperty $object, $value){
			return (string) $value . ' test';
		})
		;

		$result = $obj->get('name');
		$this->assertEquals(' test', $result);
	}

	/**
	 *
	 */
	public function testPropertyWithSpecificSetterThenSetAndGet()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->setter(function(DynamicProperty $object, $value){
			$object->rawSet($value . ' test');
			return $object->getObject();
		})
		;

		$obj->set('name', 'set');
		$value = $obj->get('name');
		$this->assertEquals('set test', $value);
	}

	/**
	 *
	 */
	public function testPropertyWithGetterBeforeAndAfterListeners()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->listener('before', 'get', function(DynamicProperty $property, $value) use($test){
			$test->assertInstanceOf('se\DynObject\DynamicProperty', $property);
			$test->assertEquals(null, $value);
		})
		->listener('after', 'get', function(DynamicProperty $property, $value, $result) use($test){
			$test->assertInstanceOf('se\DynObject\DynamicProperty', $property);
			$test->assertEquals(null, $value);
			$test->assertEquals(null, $result);
		})
		;

		$obj->get('name');
	}

	/**
	 *
	 */
	public function testPropertyWithSetterBeforeAndAfterListeners()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->listener('before', 'set', function(DynamicProperty $property, $actual, $requested) use($test){
			$test->assertInstanceOf('se\DynObject\DynamicProperty', $property);
			$test->assertEquals(null, $actual);
			$test->assertEquals('value', $requested);
		})
		->listener('after', 'set', function(DynamicProperty $property, $actual, $requested) use($test){
			$test->assertInstanceOf('se\DynObject\DynamicProperty', $property);
			$test->assertEquals('value', $actual);
			$test->assertEquals('value', $requested);
			$test->assertEquals($actual, $requested);
		})
		;

		$obj->set('name', 'value');
	}


	/**
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testPropertyWithTypeAndSetterThenSetWrongType()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->setType('se\DynObject\Test\Libs\TestClass')
		;

		$obj->set('name', 'value');
	}

	/**
	 *
	 */
	public function testPropertyWithTypeAndSetterThenSetExpectedType()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->setType('se\DynObject\Test\Libs\TestClass')
		;

		$obj->set('name', new TestClass());
	}

	/**
	 *
	 */
	public function testPropertyWithDefaultValueDatatypeThenGet()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->setDefault('Stephane')
		;

		$this->assertEquals('Stephane', $obj->get('name'));
	}

	/**
	 *
	 */
	public function testPropertyWithDefaultValueClosureThenGet()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->setDefault(function(DynamicProperty $property) use($test){
			$test->assertInstanceOf('se\DynObject\DynamicProperty', $property);
			return 'Stephane';
		})
		;

		$value = $obj->get('name');
		$this->assertEquals('Stephane', $value);
	}

	/**
	 *
	 */
	public function testGetInexistantPropertyGetGivenDefault()
	{
		$test = $this;
		$obj = $this->getDynamicObject();

		$value = $obj->get('name', 'default');
		$this->assertEquals('default', $value);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testCallRawGetOnPropertyWhenLocked()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		;

		$property = $obj->property('name');
		$property->rawGet();
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testCallRawSetOnPropertyWhenLocked()
	{
		$test = $this;
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		;

		$property = $obj->property('name');
		$property->rawSet('test');
	}


	public function testGetReflection()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		;

		$ref = $obj->property('name')->getReflection();

		$this->assertInstanceOf('ReflectionProperty', $ref);
	}
	
	public function testGetDefaultWhenDefinedPossible()
	{
		$obj = $this->getDynamicObject();
		$obj
		->property('name')
		->withGetter()
		->withSetter()
		->setDefault('test')
		;
		
		$this->assertEquals('test', $obj->get('name'));		
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
