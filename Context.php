<?php

namespace Smally;

class Context {

	protected $_application = null;

	protected $_vars = array();

	/**
	 * Construct the global $context object
	 * @param \Smally\Application $application reverse reference to the application
	 * @param array $vars Context object $vars
	 */
	public function __construct(\Smally\Application $application, array $vars){
		$this->setApplication($application);
		if($vars){
			$this->_vars = $vars;
		}
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
		return $this->_vars[$name] = $value;
	}

	/**
	 * You can get $_REQUEST object style
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
	}

	/**
	 * Return the IP of client
	 * @return string
	 */
	public function getIp(){
		return getenv('REMOTE_ADDR');
	}
}