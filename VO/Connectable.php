<?php

namespace Smally\VO;

/**
 * Standard Value Object that's has Connectable and Rightable possibility , implements Acl Role Interface
 */
class Connectable extends Standard implements \Smally\InterfaceAclRole {

	static $_connectable = null;


	/**
	 * Same as Standard constructor but also define rights
	 * @param array $vars [description]
	 */
	public function __construct($vars=array()){
		parent::__construct($vars);
		if(isset($this->rights)){
			$this->setRight($this->rights);
		}
	}

	/**
	 * Return the \Smally\Connectable instance
	 * @return \Smally\Connectable
	 */
	public function getConnectable(){
		if(is_null(static::$_connectable)){
			static::$_connectable = new \Smally\Connectable(get_called_class());
		}
		return static::$_connectable;
	}

	public function getRightable(){
		if(is_null($this->_rightable)){
			$this->_rightable = new \Smally\Rightable();
		}
		return $this->_rightable;
	}

	/**
	 * Wrapper to \Smally\Connectable methods
	 * @param  string $name The name of the method
	 * @param  array $args Arguments of the method
	 * @return mixed
	 */
	public function __call($name,$args){
		if(method_exists($this->getConnectable(), $name)){
			return call_user_func_array(array($this->getConnectable(),$name), $args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}
	static public function __callStatic($name,$args){
		if(method_exists(static::getConnectable(), $name)){
			return call_user_func_array(array(static::getConnectable(),$name), $args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}

	/**
	 * Must be clearly defined due to acl interface
	 * @return \Smally\VO\Connectable
	 */
	static public function getInstance(){
		return static::getConnectable()->getInstance();
	}


	/**
	 * Put the specific right to the model
	 * @param int $right
	 */
	public function setRight($right){
		return $this->getRightable()->setRight($right);
	}

	/**
	 * Get rights of the model
	 * @param boolean $array
	 * @return mixed
	 */
	public function getRight($array=false){
		return $this->getRightable()->getRight($array);
	}

	/**
	 * Is their this right in the model
	 * @param int $right
	 */
	public function hasRight($right){
		return $this->getRightable()->hasRight($right);
	}

}