<?php

namespace Smally;

class Connectable {

	protected $_objectName = null;

	public function __construct($objectName){
		$this->_objectName = $objectName;
	}

	/**
	 * Return the object name of the connectable
	 * @return string
	 */
	public function getObjectName(){
		return $this->_objectName;
	}

	/**
	 * Return the uniqname for session
	 */
	public function getUniqName(){
		return $this->_objectName.'Connectable';
	}

	/**
	 * Put the current model to session
	 */
	public function connect($object){
		$sessionName = $this->getUniqName();
		Session::getInstance()->{$sessionName} = $object;
	}

	/**
	 * Remove the session for the model
	 */
	public function deconnect(){
		$sessionName = $this->getUniqName();
		Session::getInstance()->{$sessionName} = null;
	}

	/**
	 * Is the model actually in session
	 * @return boolean
	 */
	public function isConnected(){
		$objectName = $this->getObjectName();
		return ( Session::getInstance()->{$this->getUniqName()} instanceof $objectName );
	}

	/**
	 * Get the model actually in session
	 * @return \Smally\User
	 */
	public function getInstance(){
		if($this->isConnected()){
			return Session::getInstance()->{$this->getUniqName()};
		}
		return null;
	}

}
