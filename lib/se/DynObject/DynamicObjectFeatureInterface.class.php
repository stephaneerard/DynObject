<?php
namespace se\DynObject;

interface DynamicObjectFeatureInterface
{
	/**
	 * 
	 * @param stdClass $object
	 * @return DynamicMethodInterface
	 */
	public function setObject($object);
	
	
	/**
	 * 
	 * @return stdClass
	 */
	public function getObject();
	
	/**
	 * 
	 * @return Reflection
	 */
	public function getReflection($opts = null);
}