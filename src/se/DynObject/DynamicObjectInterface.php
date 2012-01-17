<?php
namespace se\DynObject;

interface DynamicObjectInterface
{
	/**
	 * 
	 * @param string $class
	 * @return DynamicObjectInterface
	 */
	static public function instanciate($class = null);
	
	
	/**
	 * @param string $name
	 * @param DynamicMethod $method
	 * @return DynamicMethodInterface
	 */
	public function method($name, DynamicMethodInterface $method = null, $class = '');
	
	/**
	 * 
	 * @param string $name
	 * @param DynamicPropertyInterface $property
	 * @param string $class
	 * @return DynamicPropertyInterface
	 */
	public function property($name, DynamicPropertyInterface $property = null, $class = '');
}