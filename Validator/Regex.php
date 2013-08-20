<?php

namespace Smally\Validator;

class Regex extends AbstractRule {

	protected $_regex = '#^.*$#'; // default rule match all

	/**
	 * Construct the validator with a particular $regex if provided
	 * @param string $regex A valid preg_match regex
	 */
	public function __construct($regex=null){
		$this->setRegex($regex);
	}

	/**
	 * Set the regex the value must match
	 * @param string $regex A valid preg_match regex
	 * @return  \Smally\Validator\Regex
	 */
	public function setRegex($regex){
		if($regex){
			$this->_regex = $regex;
		}
		return $this;
	}
	/**
	 * Validate if the $valueToTest is of the $_regex format
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		$valueToTest = (string) $valueToTest;

		if(preg_match($this->_regex, $valueToTest)){
			return true;
		}else $this->addError(__('VALIDATOR_REGEX_ERROR_FORMAT'));

		return false;
	}

}