<?php

namespace Smally\Validator;

class Date extends AbstractRule {

	protected $_format = null;

	protected static $_validFormat = array(
			'fr',
			'en',
			'int',
			'unix'
		);

	/**
	 * Construct the rule with options
	 * @param string  $format    format of the expected date (fr, en, int)
	 */
	public function __construct($format='fr'){
		$this->setFormat($format);
	}

	/**
	 * Set the expected format
	 * @param string $format
	 * @return \Smally\Validator\Date
	 */
	public function setFormat($format){
		if(in_array($format, self::$_validFormat)){
			$this->_format = $format;
		}else throw new \Smally\Exception('Invalid date validator format !');
		return $this;
	}

	/**
	 * Validate if the $valueToTest is of the attended date $format
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		$valueToTest = (string) $valueToTest;

		switch($this->_format){
			default:
			case 'fr':
				$regex = '#^(0[1-9]|[12][0-9]|3[01])([/.])(0[1-9]|1[0-2])\\2([0-9]{2}|[0-9]{4})$#';
				if(preg_match($regex,$valueToTest,$matches)){
					return true;
				}else $this->addError(__('VALIDATOR_DATE_ERROR_FORMAT_FR'));
			break;
			case 'en':
				$regex = '#^(0[1-9]|1[0-2])([/.])(0[1-9]|[12][0-9]|3[01])\\2([0-9]{2}|[0-9]{4})$#';
				if(preg_match($regex,$valueToTest,$matches)){
					return true;
				}else $this->addError(__('VALIDATOR_DATE_ERROR_FORMAT_EN'));
			break;
			case 'int':
				$regex = '#^([0-9]{2}|[0-9]{4})([/.])(0[1-9]|1[0-2])\\2(0[1-9]|[12][0-9]|3[01])$#';
				if(preg_match($regex,$valueToTest,$matches)){
					return true;
				}else $this->addError(__('VALIDATOR_DATE_ERROR_FORMAT_INT'));
			break;
			case 'unix':
				$regex = '#^([0-9]{1,})$#';
				if(preg_match($regex,$valueToTest,$matches)){
					return true;
				}else $this->addError(__('VALIDATOR_DATE_ERROR_FORMAT_UNIX'));
			break;
		}
		return false;
	}

}