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

		$regex = '#^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+([A-Z]{2,4}|museum)$#i';

		if(preg_match($regex, $valueToTest)){
			return true;
		}else $this->addError('Ceci n\'est pas une adresse email valide.');

		return false;
	}

}