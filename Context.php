<?php

namespace Smally;

class Context {

	protected $_application = null;

	protected $_vars = null;

	/**
	 * Construct the global $context object
	 * @param \Smally\Application $application reverse reference to the application
	 * @param array $vars Context object $vars
	 */
	public function __construct(\Smally\Application $application, array $vars){
		$this->setApplication($application);
		$this->_vars = new ContextStdClass($vars);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Context
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * You can set $_REQUEST object style
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set($name,$value){
		return $this->_vars->{$name} = $value;
		//return $this->_vars[$name] = $value;
	}

	/**
	 * You can get $_REQUEST object style
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		return $this->_vars->{$name} ;
		//return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
	}

	/**
	 * Return the IP of client
	 * @return string
	 */
	public function getIp(){
		return getenv('REMOTE_ADDR');
	}

	/**
	 * Return an array representation of the context
	 * @return [type] [description]
	 */
	public function toArray(){
		return $this->_vars->toArray();
	}
}

/**
 * Specific class for Context object
 */
class ContextStdClass extends \stdClass {

	/**
	 * Put $vars as object property
	 * @param array $vars An array of properties
	 */
	public function __construct($vars=array()){
		if($vars){
			foreach($vars as $name => $value){
				$this->{$name} = $value;
			}
		}
	}

	/**
	 * Automatically create a new property with string value or a sub ContextStdClass object
	 * @param string $name  name of the property
	 * @param mixed $value value of the property
	 */
	public function __set($name,$value){
		if(is_array($value)){
			$this->{$name} = new self($value);
		}else{
			$this->{$name} = $value;
		}
	}

	/**
	 * If we get an undefined property, we return an empty ContextStdClass to allow direct try of ->toto->tata->titi even if toto is not defined
	 * @param  string $name The name of the undefined property
	 * @return \Smally\ContextStdClass
	 */
	public function __get($name){
		return $this->{$name} = new self();
	}

	/**
	 * Convert the class to an array representation ( recursive )
	 * @return array
	 */
	public function toArray(){
		$array = array();
		foreach($this as $key => $value){
			if($value instanceof ContextStdClass){
				$array[$key] = $value->toArray();
			}else{
				$array[$key] = $value;
			}
		}
		return $array;
	}

}