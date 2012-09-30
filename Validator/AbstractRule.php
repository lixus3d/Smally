<?php

namespace Smally\Validator;

abstract class AbstractRule implements InterfaceRule {

	protected $_errors = array();

	/**
	 * Add an error to the current rule
	 * @param string $error Error text
	 */
	public function addError($error=''){
		$this->_errors[] = $error;
		return $this;
	}

	/**
	 * Get the rule errors
	 * @return array
	 */
	public function getError(){
		return $this->_errors;
	}



}