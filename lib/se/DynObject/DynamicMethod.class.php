<?php

namespace se\DynObject;

class DynamicMethod extends DynamicObjectFeature implements DynamicMethodInterface
{
	protected $_impls = array();
	protected $_impl;

	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::define()
	 */
	public function implementation($function, $name = 'default', $default = false)
	{
		$this->_impls[$name] = $function;
		if($default)
		{
			$this->setCurrentImplementation($name);
		}
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::setImplementation()
	 */
	public function setCurrentImplementation($name)
	{
		$this->_impl = $name;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::getImplementation()
	 */
	public function getCurrentImplementation()
	{
		return $this->_impl;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::call()
	 */
	public function call()
	{
		$args = func_get_args();
		$this->executeListeners('before', 'call', array_merge(array($this->getObject()), $args));
		$result = call_user_func_array($this->_impls[$this->_impl], array_merge(array($this->getObject()), $args));
		$this->executeListeners('after', 'call', array_merge(array($this->getObject(), $result), $args));
		
		$this->_result = $result;
		return $this;
	}
	
	public function result()
	{
		return $this->_result;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return ReflectionFunction
	 */
	public function getReflection($name = null)
	{
		$name = null === $name ? $this->_impl : $name;
		return new ReflectionFunction($this->_impls[$name]);
	}
	
	public function __invoke($args)
	{
		$this->call($args);
	}
}