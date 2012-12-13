<?php

namespace Smally;

class Session {

	static protected $_singleton = null;
	protected $_vars = array();

	/**
	 * Construct a new Session object and start the session
	 */
	public function __construct(){
		session_start();
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
	}

	/**
	 * Set the singleton instance of Session
	 * @return \Smally\Session
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Return the singleton
	 * @return \Smally\Session
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof Session){
			new self();
		}
		return self::$_singleton;
	}

	/**
	 * You can set $_SESSION property in object style
	 * @param string $name
	 * @param mixed $value
	 * @return string
	 */
	public function __set($name,$value){
		return $_SESSION[$name] = $value;
	}

	/**
	 * You can access $_SESSION object style
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}

	/**
	 * Reset the $_SESSION array to an empty array
	 * @return \Smally\Session
	 */
	public function reset(){
		$_SESSION = array();
		return $this;
	}

}