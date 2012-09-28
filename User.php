<?php

namespace Smally;


class User {

	protected $_rights = 0;

	/**
	 * Return the uniqname for session
	 */
	static public function getUniqName(){
		return get_called_class().'Connected';
	}

	/**
	 * Put the current model to session
	 */
	public function connect(){
		$sessionName = static::getUniqName();
		Session::getInstance()->{$sessionName} = $this;
	}

	/**
	 * Remove the session for the model
	 */
	static public function deconnect(){
		$sessionName = static::getUniqName();
		Session::getInstance()->{$sessionName} = null;
	}

	/**
	 * Is the model actually in session
	 * @return boolean
	 */
	static public function isConnected(){
		return (Session::getInstance()->{static::getUniqName()} instanceof static);
	}

	/**
	 * Get the model actually in session
	 * @return \Smally\User
	 */
	static public function getInstance(){
		if(static::isConnected()){
			return Session::getInstance()->{static::getUniqName()};
		}
		return null;
	}

	/**
	 * Put the specific right to the model
	 * @param int $right
	 */
	public function setRight($right){
		$this->_rights |= $right;
		return $this;
	}

	/**
	 * Get rights of the model
	 * @param boolean $array
	 * @return mixed
	 */
	public function getRight($array=false){
		if($array){
			$return = array();
			for($i=1;$i<=63;$i++){
				$pow = pow(2,$i);
				if( $pow & $this->_rights ) $return[] = $pow;
			}
			return $return;
		}
		return $this->_rights;
	}

	/**
	 * Is their this right in the model
	 * @param int $right
	 */
	public function hasRight($right){
		return ($this->_rights & $right)? true : false ;
	}

}
