<?php

namespace Smally;

class Validator {

	protected $_testValues = array();
	protected $_rules = array();
	protected $_errors = array();

	/**
	 * You can pass directly the values to test to the constructor
	 * @param array $testValues [description]
	 */
	public function __construct($testValues=array()){
		if(method_exists($this, 'init')){
			$this->init();
		}
		$this->setTestValues($testValues);
	}

	/**
	 * Set the testValues of the validator
	 * @param array  $testValues The values to test
	 */
	public function setTestValues($testValues){
		$this->_testValues = $testValues;
		return $this;
	}

	/**
	 * Add a validator rule to the field $field
	 * @param string                          $field      The name of the field to test
	 * @param \Smally\Validator\InterfaceRule $ruleObject The rule object to apply to the field
	 * @return \Smally\Validator
	 */
	public function addRule($field,\Smally\Validator\InterfaceRule $ruleObject){
		if(!isset($this->_rules[$field])) $this->_rules[$field] = array();
		$this->_rules[$field][] = $ruleObject;
		return $this;
	}


	/**
	 * Add errors to the given $field
	 * @param string $field  the fieldName you want to add an error to
	 * @param array  $errors Array of error string
	 */
	public function addError($field, array $errors){
		if(!isset($this->_errors[$field])) $this->_errors[$field] = array();
		$this->_errors[$field] = array_merge($this->_errors[$field],$errors);
		return $this;
	}


	/**
	 * Return the validator errors
	 * @return array
	 */
	public function getError(){
		return $this->_errors;
	}

	/**
	 * Return a test value by it's fieldName
	 * @param  string $fieldName The fieldName of the value you want to test
	 * @return mixed
	 */
	public function getValue($fieldName){
		return isset($this->_testValues[$fieldName])?$this->_testValues[$fieldName]:null;
	}

	/**
	 * Empty error table
	 * @return \Smally\Validator
	 */
	public function resetError(){
		$this->_errors = array();
		return $this;
	}

	/**
	 * Execute the validator rules on $fields
	 * @param  boolean $stopAtFirstError Do we stop the test of each field at the first error find , default is true
	 * @return boolean
	 */
	public function x($stopAtFirstError=true){
		$this->resetError();
		foreach($this->_rules as $field => $rules){
			$fieldValue = $this->getValue($field);
			foreach($rules as $rule){
				if(!$rule->x($fieldValue)){
					$this->addError($field, $rule->getError());
					if($stopAtFirstError) break;
				}
			}
		}
		return count($this->_errors)?false:true; // if we have errors then return false
	}







}