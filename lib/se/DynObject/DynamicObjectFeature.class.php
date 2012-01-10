<?php
namespace se\DynObject;

abstract class DynamicObjectFeature implements DynamicObjectFeatureInterface
{
	protected $_listeners = array('before' => array(), 'after' => array());
	protected $_obj;

	public function listener($when, $for, $function, $name = null)
	{
		if(!isset($this->_listeners[$when][$for]))
		{
			$this->_listeners[$when][$for] = array();
		}
		
		if($name)
		{
			$this->_listeners[$when][$for][$name] = $function;
		}
		else
		{
			$this->_listeners[$when][$for][] = $function;
		}
		
		return $this;
	}
	
	public function removeListener($when, $for, $name)
	{
		unset($this->_listeners[$when][$for][$name]);
	}
	
	protected function executeListeners($when, $for, $args = array())
	{
		if(!isset($this->_listeners[$when][$for])) return ;
		
		foreach($this->_listeners[$when][$for] as $callable)
		{
			call_user_func_array($callable, $args);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::setObject()
	 */
	public function setObject($object)
	{
		$this->_obj = $object;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see DynamicMethodInterface::getObject()
	 */
	public function getObject()
	{
		return $this->_obj;
	}
}