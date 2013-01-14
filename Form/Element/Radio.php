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
		if($values instanceof \Smally\ContextStdClass) $values = $values->toArray();
		if(!is_array($values)) $values = array($values);
		$currentValue = $this->getValue();
		if(is_array($currentValue)){
			foreach($values as $value){
				if(array_key_exists($value,$currentValue)){
					$this->_checked[] = $value;
				}
			}
		}
		return $this;
 	}

}