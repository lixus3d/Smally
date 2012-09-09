<?php

namespace Smally;

class Context {

	protected $_application = null;

	protected $_vars = array();

	/**
	 * Construct the global $context object
	 * @author Lixus3d <developpement@adreamaline.com>
	 * @param array $vars
	 */
	public function __construct(\Smally\Application $application, array $vars){
		$this->setApplication($application);
		if($vars){
			$this->_vars = $vars;
		}
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	public function getApplication(){
		return $this->_application;
	}
	/**
	 * You can access $_REQUEST object style , and set element if you want ...
	 * @author Lixus3d <developpement@adreamaline.com>
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value){
		return $this->_vars[$name] = $value;
	}

	/**
	 * You can access $_REQUEST object style
	 * @author Lixus3d <developpement@adreamaline.com>
	 * @param string $name
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