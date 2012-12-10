<?php

namespace Smally\Validator;

abstract class AbstractRule implements InterfaceRule {

	protected $_errors = array();

	protected $_labelAdd = null;
	protected $_helpAdd = null;

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

	/**
	 * Get the addition to field label defined by the validator
	 * @return string
	 */
	public function getLabelAdd(){
		return $this->_labelAdd;
	}

	/**
	 * Get the addition to field help defined by the validator
	 * @return string
	 */
	public function getHelpAdd(){
		return $this->_helpAdd;
	}


}