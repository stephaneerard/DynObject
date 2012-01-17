<?php
namespace se\DynObject;

use se\DynObject\Exceptions\MethodNotFoundException;

class DynamicObject implements DynamicObjectInterface
{

	protected $_dynamics = array('method' => array(), 'property' => array());
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $class
	 * @return DynamicObjectInterface
	 */
	static public function instanciate($class = null)
	{
		$class = null === $class ? 'se\\DynObject\\DynamicObject' : $class;
		return new $class();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DynamicObjectInterface::method()
	 */
	public function method($name, DynamicMethodInterface $method = null, $class = 'se\DynObject\DynamicMethod')
	{
		return $this->_feature('method', $name, $method, $class);
	}
	
	public function hasMethod($name)
	{
		return $this->_hasFeature('method', $name);
	}
	
	public function property($name, DynamicPropertyInterface $property = null, $class = 'se\DynObject\DynamicProperty')
	{
		return $this->_feature('property', $name, $property, $class);
	}
	
	public function hasProperty($name)
	{
		return $this->_hasFeature('property', $name);
	}
	
	protected function _hasFeature($type, $name)
	{
		return isset($this->_dynamics[$type][$name]);
	}
	
	protected function _feature($type, $name, DynamicObjectFeatureInterface $feature = null, $class)
	{
		if(null === $feature)
		{
			if(!isset($this->_dynamics[$type][$name]))
			{
				$this->_dynamics[$type][$name] = new $class();
				$this->_dynamics[$type][$name]->setObject($this);
			}
			$feature = $this->_dynamics[$type][$name];
			$feature->setObject($this);
		}
		else
		{
			$this->_dynamics[$type][$name] = $feature;
		}
		
		return $feature;
	}
	
	public function get($name, $default = null)
	{
		if(!isset($this->_dynamics['property'][$name]))
		{
			return $default;
		}
		
		return $this->_dynamics['property'][$name]->get();
	}
	
	public function set($name, $value)
	{
		$this->_dynamics['property'][$name]->set($value);
		
		return $this;
	}
	
	public function call($method, $args = array(), $result = true, $impl = null)
	{
		if(!isset($this->_dynamics['method'][$method]))
		{
			return $this->callMethodNoutFound($method, $args, $result, $impl);
		}
		
		$method = $this->_dynamics['method'][$method];
		if($impl)
		{
			$method->setCurrentImplementation($impl);
		}
		
		$method->call($args);
		
		if($result)
		{
			return $method->result();
		}
		
		return $this;
	}
	
	protected function callMethodNoutFound($method, $args, $result, $impl)
	{
		if(isset($this->_methodNotFoundHandler))
		{
			return call_user_func_array($this->_methodNotFoundHandler, array($this, $method, $args, $result, $impl));
		}
		
		throw new MethodNotFoundException();
	}
	
	public function setMethodNotFoundHandler($function)
	{
		$this->_methodNotFoundHandler = $function;
		return $this;
	}
}