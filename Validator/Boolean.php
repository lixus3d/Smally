<?php

namespace Smally\Validator;

class Boolean extends AbstractRule {

	/**
	 * Validate if the $valueToTest is an integer (as numeric)
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		if(is_array($valueToTest)) $valueToTest = array_shift($valueToTest);

		if($valueToTest===''||$valueToTest===null) $valueToTest = false;

		if(!($test = is_bool($valueToTest))){
			$this->addError(__('VALIDATOR_BOOLEAN_ERROR'));
		}
		return $test;
	}

}