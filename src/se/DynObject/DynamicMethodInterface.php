<?php
namespace se\DynObject;

interface DynamicMethodInterface extends DynamicObjectFeatureInterface
{
	/**
	 * 
	 * @param string $name
	 * @param Closure $function
	 * @param boolean $default
	 * @return DynamicMethodInterface
	 */
	public function implementation($function, $name = 'default', $default = false);
	
	
	/**
	 * 
	 * @param string $name
	 * @return DynamicMethodInterface
	 */
	public function setCurrentImplementation($name);

	/**
	 * 
	 * @return string
	 */
	public function getCurrentImplementation();
	
	/**
	 * 
	 * @return DynamicMethodInterface
	 */
	public function call();

	
	/**
	 * 
	 * @return mixed
	 */
	public function result();
	
}