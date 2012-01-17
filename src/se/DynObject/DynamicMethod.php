<?php

namespace se\DynObject;

use se\DynObject\Exceptions\MethodImplementationNotFoundException;
use se\DynObject\Exceptions\MethodNotFoundException;

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
		if(!isset($this->_impls[$this->_impl]))
		{
			throw new MethodImplementationNotFoundException();
		}
		if(func_num_args()>1){
			$args = func_get_args();
		}
		else
		{
			$args = (array) func_get_arg(0);
		}
		$passedArgs = array_merge(array($this->getObject()), $args);
		$this->executeListeners('before', 'call', $passedArgs);
		$result = call_user_func_array($this->_impls[$this->_impl], $passedArgs);
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
		return new \ReflectionFunction($this->_impls[$name]);
	}

	public function __invoke()
	{
		$args = func_get_args();
		return $this->call($args);
	}
}