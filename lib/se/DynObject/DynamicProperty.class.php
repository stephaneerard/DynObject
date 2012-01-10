<?php
namespace se\DynObject;

class DynamicProperty extends DynamicObjectFeature implements DynamicPropertyInterface
{
	protected $_getter;
	protected $_setter;
	protected $_type;
	protected $_value;
	protected $_allowsNull;
	protected $_hasDefault = false;
	protected $_default;
	protected $_isSet = false;
	protected $_locked = true;
	
	public function get()
	{
		if(!$this->_getter)
		{
			throw new \RuntimeException('No logic assigned for this setter');
		}
		
		$this->_locked = false;
		$this->executeListeners('before', 'get', array($this, $this->_value));
		if($this->hasDefault() && !$this->isDefined())
		{
			$result = $this->getDefault();
		}
		else
		{
			$result = call_user_func_array($this->_getter, array($this, $this->_value));
		}
		$this->executeListeners('after', 'get', array($this, $this->_value, $result));
		$this->_locked = true;
		
		return $result;
	}
	
	public function rawGet()
	{
		if($this->_locked)
		{
			throw new LogicException();
		}
		return $this->_value;
	}
	
	public function set($value)
	{
		if(!$this->_setter)
		{
			throw new \RuntimeException('No logic assigned for this setter');
		}
		
		if($this->hasType() && !$this->isValueOfValidType($value))
		{
			throw new \InvalidArgumentException();
		}
		
		$this->_locked = false;
		$this->executeListeners('before', 'set', array($this, $this->_value, $value));
		$result = call_user_func_array($this->_setter, array($this, $value));
		$this->executeListeners('after', 'set', array($this, $this->_value, $value));
		$this->_locked = true;
		
		
		return $result;
	}
	
	public function rawSet($value)
	{
		if($this->_locked)
		{
			throw new \LogicException();
		}
		$this->_value = $value;
	}
	
	public function isValueOfValidType($value)
	{
		$type = $this->_type;
		if($type == 'array' && is_array($value)) return true;
		elseif($type == 'string' && is_string($value)) return true;
		elseif($type == 'integer' && is_integer($value)) return true;
		elseif($type == 'float' && is_float($value)) return true;
		elseif($value instanceof $type) return true;
		
		return false;
	}
	
	public function hasDefault()
	{
		return $this->_hasDefault;
	}
	
	public function isDefined()
	{
		return $this->_isSet;
	}
	
	public function setDefault($value)
	{
		$this->_hasDefault = true;
		$this->_default = $value;
		return $this;
	}
	
	public function getDefault()
	{
		if($this->_default instanceof \Closure)
		{
			return $this->_default->__invoke($this);
		}
		return $this->_default;
	}
	
	public function setType($type)
	{
		$this->_type = $type;
		return $this;
	}
	
	public function hasType()
	{
		return null !== $this->_type;
	}
	
	public function withGetter()
	{
		if(null !== $this->_getter) return $this;
		$this->_getter = function(DynamicPropertyInterface $property){
			if(!$property->isDefined() && $property->hasDefault())
			{
				return $property->getDefault();
			}
			return $property->rawGet();
		};
		return $this;
	}
	
	public function withSetter()
	{
		if(null !== $this->_setter) return $this;
		$this->_setter = function(DynamicPropertyInterface $property, $value){
			$property->rawSet($value);
			return $property->getObject();
		};
		return $this;
	}
	
	public function setter(\Closure $function)
	{
		$this->_setter = $function;
		return $this;
	}
	
	public function getter(\Closure $function)
	{
		$this->_getter = $function;
		return $this;
	}
	
	public function getReflection($opts = null)
	{
		return new \ReflectionProperty($this, '_value');
	}
}