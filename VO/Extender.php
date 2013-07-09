<?php

namespace Smally\VO;

class Extender {

	protected $_parentVo = null;

	/**
	 * Must be constructed with the current vo
	 * @param \Smally\VO\Standard $parentVo The current vo you want to extend
	 */
	public function __construct( \Smally\VO\Standard $parentVo){
		$this->_parentVo = $parentVo;
	}

	/**
	 * When the property doesn't exist in extender, try to get it from the parent vo
	 * @return mixed
	 */
	public function __get($name){
		return $this->_parentVo->{$name};
	}

	/**
	 * When the property doesn't exist in extender, try to set it in the parent vo
	 * @return mixed
	 */
	public function __set($name,$value){
		return $this->_parentVo->{$name} = $value ;
	}

	/**
	 * When the method doesn't exist in object, try to execute it in the parent vo
	 * @return mixed
	 */
	public function __call($name,$args){
		if(method_exists($this->_parentVo, $name)){
			return call_user_func_array(array($this->_parentVo,$name),$args);
		}else{
			throw new \Exception('Method '.$name.' doesn\'t exist in extender');
		}
	}

}