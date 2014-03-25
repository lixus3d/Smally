<?php

namespace Smally;

/**
 * Generic Logger class for all your logs
 */

class Messaging {

	const LVL_INFO 		= 1;
	const LVL_WARNING 	= 2;
	const LVL_ERROR 	= 3;

	static protected $_singleton 	= null;

	protected $_application = null;

	/**
	 * Construct a new Messaging object and start the session
	 */
	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
	}

	/**
	 * Set the singleton instance of Messaging
	 * @return \Smally\Messaging
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Return the singleton
	 * @return \Smally\Messaging
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof Messaging){
			new self();
		}
		return self::$_singleton;
	}

	public function addMesage(){

	}


}