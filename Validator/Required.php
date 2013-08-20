<?php

namespace Smally\Validator;

class Required extends AbstractRule {

	protected $_labelAdd = ' <span class="required">*</span>';

	/**
	 * Validate if the $valueToTest is filled
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){
		if(!($test = $valueToTest?true:false)){
			$this->addError(__('VALIDATOR_REQUIRED_ERROR'));
		}
		return $test;
	}

}