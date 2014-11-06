<?php

namespace Smally\Validator;

class Regex extends AbstractRule {

	protected $_regex = '#^.*$#'; // default rule match all
	protected $_errorTxt = null;

	/**
	 * Construct the validator with a particular $regex if provided
	 * @param string $regex A valid preg_match regex
	 */
	public function __construct($regex=null,$errorTxt=null){
		if(is_null($errorTxt)) $errorTxt = __('VALIDATOR_REGEX_ERROR_FORMAT');
		$this->setRegex($regex);
		$this->_errorTxt = $errorTxt;
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
		}else $this->addError($this->_errorTxt);

		return false;
	}

}