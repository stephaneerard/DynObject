<?php

namespace se\DynObject\Test;

use se\DynObject\DynamicProperty;

use se\DynObject\DynamicObject;

class DynamicPropertyTestCase extends \PHPUnit_Framework_TestCase
{
	public function testNew()
	{
		$new = DynamicObject::instanciate();
		$this->assertInstanceOf('se\DynObject\DynamicObject', $new);
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
		->setType('TestClass')
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
		->setType('se\DynObject\Test\TestClass')
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


class TestClass{}