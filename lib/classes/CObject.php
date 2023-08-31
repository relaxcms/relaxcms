<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CObject
{	
	protected $_messages = array();
	protected $_messages_level = array();
	
	public function __construct($options=array()) 
	{
	}
		
	public function CObject($options=array())
	{
		$this->__construct($options);
	}
		
	public function getProperty($property, $default=null)
	{
		if (isset($this->$property)) {
			return $this->$property;
		}
		return $default;
	}
	
	public function setProperty($property, $value=null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;
		return $previous;
	}
	
	function getProperties($public = true)
	{
		$vars  = get_object_vars($this);
		if ($public){
			foreach ($vars as $key => $value){
				if ('_' == substr($key, 0, 1)) {
					unset($vars[$key]);
				}
			}
		}
		
		return $vars;
	}

	function setProperties($properties)
	{
		$properties = (array) $properties; 
		
		if (is_array($properties)) {
			foreach ($properties as $k => $v) {
				$this->$k = $v;
			}
			
			return true;
		}
		
		return false;
	}
	

	public function getMessage($index = null, $toString = true)
	{
		if ( $index === null) {
			$error = end($this->_messages);
		} else {
			if ( ! array_key_exists($i, $this->_messages) ) {
				return false;
			}
			else {
				$error	= $this->_messages[$i];
			}
		}
		return $error;
	}

	public function getMessages()
	{
		return $this->_messages;
	}
	
	public function getMessagesLevel()
	{
		return $this->_messages_level;
	}	
	
	public function setMessage($msg, $level=0)
	{
		if (!isset($this->_messages[$level])) {
			$this->_messages[$level] = array();
		}
		array_push($this->_messages[$level], $msg);
	}
	
	public function cleanMessage()
	{
		$this->_messages = array();
	}
	

	public function toString()
	{
		return get_class($this);
	}
}
