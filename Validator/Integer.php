<?php

namespace Smally\Validator;

class Integer extends AbstractRule {

	/**
	 * Validate if the $valueToTest is an integer (as numeric)
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		if(is_array($valueToTest)) $valueToTest = array_shift($valueToTest);

		if($valueToTest===''||$valueToTest===null) $valueToTest = 0;

		if(!($test = is_numeric($valueToTest))){
			$this->addError(__('VALIDATOR_INTEGER_ERROR'));
		}
		return $test;		
	}

}