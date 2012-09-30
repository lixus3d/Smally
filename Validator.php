<?php

namespace Smally;

class Validator {

	protected $_rules = array();
	protected $_errors = array();

	public function x($stopAtFirstError=true){
		$this->resetError();
		foreach($this->_rules as $rule){
			if(!$rule->x()){
				$this->addError($rule->getError());
			}
		}
		return count($this->_errors)?false:true; // if we have errors then return false
	}

	public function resetError(){
		$this->_errors = array();
		return $this;
	}

	public function getError(){
		return $this->_errors;
	}

	public function addError(array $errors){
		$this->_errors += $errors;
		return $this;
	}

}