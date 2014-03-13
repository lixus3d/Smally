<?php

namespace Smally\Form\Element;

class Voselect extends AbstractElement{

	protected $_type = 'select';
	protected $_decorator = 'voselect';

	protected $_subVoValue = null;

	/**
	 * Reset the field state
	 * @return \Smally\Form\Element\Radio
	 */
	public function resetValue(){
		$this->_subVoValue = null;
		return $this; //parent::resetValue();
	}

	public function getSubVoValue(){
		return $this->_subVoValue;
	}

	/**
	 * Populate the value of the element, must be redefine for checkbox or radio for example to handle checked
	 * @param string $value The value of the element or the value of the option checked/selected
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function populateValue($value){
		return $this->_subVoValue = $value ;
	}

}