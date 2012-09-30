<?php

namespace Smally\Validator;

class Required extends AbstractRule {

	/**
	 * Validate if the $valueToTest is filled
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){
		if(!($test = $valueToTest?true:false)){
			$this->addError('Ce champs est obligatoire');
		}
		return $test;
	}

}