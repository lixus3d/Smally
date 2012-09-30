<?php

namespace Smally\Validator;

class Length extends AbstractRule {

	protected $_min = null;
	protected $_max = null;
	protected $_strict = false;

	/**
	 * Construct the rule with options
	 * @param int  $min    null to not test the min length
	 * @param int  $max    null to not test the max length
	 * @param boolean $strict true to have the length not equal to boundaries $min and $max
	 */
	public function __construct($min=null,$max=null,$strict=false){
		$this->setMin($min);
		$this->setMax($max);
		$this->setStrict($strict);
	}

	/**
	 * Set the min length
	 * @param int $min
	 * @return \Smally\Validator\Length
	 */
	public function setMin($min){
		$this->_min = $min;
		return $this;
	}

	/**
	 * Set the max length
	 * @param int $max
	 * @return \Smally\Validator\Length
	 */
	public function setMax($max){
		$this->_max = $max;
		return $this;
	}

	/**
	 * Set the strict logic of the rule
	 * @param boolean $strict
	 * @return \Smally\Validator\Length
	 */
	public function setStrict($strict=true){
		$this->_strict = $strict;
		return $this;
	}

	/**
	 * Validate if the $valueToTest is of the attended $length
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){
		$test = true;

		$valueToTest = (string) $valueToTest;
		$length = mb_strlen($valueToTest);

		// min test
		if(!is_null($this->_min)){
			switch(true){
				case  $this->_strict && $length<=$this->_min :
				case !$this->_strict && $length< $this->_min :
					$test = false;
					$this->addError('La longueur minimum de '.$this->_min.' caractère(s) n\'est pas atteinte');
			}
		}

		// max test
		if(!is_null($this->_max)){
			switch(true){
				case  $this->_strict && $length>=$this->_max :
				case !$this->_strict && $length> $this->_max :
					$test = false;
					$this->addError('La longueur maximum de '.$this->_max.' caractère(s) est dépassée');
			}
		}

		return $test;
	}

}