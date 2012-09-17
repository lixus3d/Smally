<?php

namespace Smally\Form\Element;

class Radio extends AbstractElement{

	protected $_type = 'radio';
	protected $_decorator = 'radio';

	protected $_checked = array();

	/**
	 * Return the selected/checked options of the Field
	 * @return array
	 */
	public function getChecked(){
		return $this->_checked;
	}

	/**
	 * We overwrite the default populate to select/check the good option
	 * @param  mixed $values the value of the option checked/selected
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function populateValue($values){
		if(!is_array($values)) $values = array($values);
		foreach($values as $value){
			if(array_key_exists($value,$this->getValue())){
				$this->_checked[] = $value;
			}
		}
		return $this;
 	}

}