<?php

namespace Smally\Validator;

class Unique extends AbstractRule {

	protected $_voName = null;
	protected $_errorTxt = 'Erreur';

	/**
	 * Construct the rule with options
	 * @param int  $min    null to not test the min length
	 * @param int  $max    null to not test the max length
	 * @param boolean $strict true to have the length not equal to boundaries $min and $max
	 */
	public function __construct($voName=null,$errorTxt=null){
		if(is_null($errorTxt)) $errorTxt = __('VALIDATOR_UNIQUE_ERROR_DEFAULT');
		$this->setVoName($voName);
		$this->_errorTxt = $errorTxt;
	}

	/**
	 * Define the voName to test for the unicity
	 * @param string $voName The vo name
	 * @return  \Smally\Validator
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	/**
	 * Get the voName to test the unicity on
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	/**
	 * Validate if the $valueToTest is filled
	 * @param  mixed $valueToTest
	 * @return boolean
	 */
	public function x($valueToTest){

		if($app = \Smally\Application::getInstance()){
			$voName = $this->getVoName();
			$dao = $app->getFactory()->getDao($voName);
			$criteria = $dao->getCriteria();
			$criteria ->setFilter(array(
							$this->getFieldName() => array('value'=>$valueToTest)
						))
						;
			if( ($this->getValidator() instanceof \Smally\Validator) && ($actualId = $this->getValidator()->getActualVoId()) ){
				$criteria->setFilter(array(
						$dao->getPrimaryKey() => array('value'=>$actualId, 'operator' => '!='),
					));
			}

			if($found = $dao->fetch($criteria)){
				$this->addError($this->_errorTxt);
				return false;
			}else{
				return true;
			}

		}else $this->addError('No valid Smally app found !');

		return false;
	}

}