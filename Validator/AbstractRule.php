<?php

namespace Smally\Validator;

abstract class AbstractRule implements InterfaceRule {

	protected $_fieldName = null;
	protected $_validator = null;

	protected $_errors = array();

	protected $_labelAdd = null;
	protected $_helpAdd = null;

	/**
	 * Add an error to the current rule
	 * @param string $error Error text
	 * @return  \Smally\Validator\AbstractRule
	 */
	public function addError($error=''){
		$this->_errors[] = $error;
		return $this;
	}

	/**
	 * Define the field name the rule will be on
	 * @param string $fieldName The field name
	 * @return  \Smally\Validator\AbstractRule
	 */
	public function setFieldName($fieldName){
		$this->_fieldName = $fieldName;
		return $this;
	}

	/**
	 * Define the back reference to the actual validator
	 * @param \Smally\Validator $validator A valid \Smally\Validator object
	 * @return  \Smally\Validator\AbstractRule
	 */
	public function setValidator($validator){
		$this->_validator = $validator;
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

	/**
	 * Get the fieldname associated with the rule
	 * @return string
	 */
	public function getFieldName(){
		return $this->_fieldName;
	}

	/**
	 * Get the back reference to the validator
	 * @return \Smally\Validator
	 */
	public function getValidator(){
		return $this->_validator;
	}



}