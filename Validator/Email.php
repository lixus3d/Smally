<?php

namespace Smally\Validator;

class Email extends AbstractRule {

	/**
	 * Validate if the $valueToTest is an email
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		$valueToTest = (string) $valueToTest;

		$regex = '#^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+([A-Z]{2,})$#i';

		if(preg_match($regex, $valueToTest)){
			return true;
		}else $this->addError(__('VALIDATOR_EMAIL_ERROR_FORMAT'));

		return false;
	}

}