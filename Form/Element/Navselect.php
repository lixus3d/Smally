<?php

namespace Smally\Form\Element;

class Navselect extends Radio{

	protected $_type = 'select';
	protected $_decorator = 'navselect';

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
				$this->_checked[] = $value;
			}
		}
		return $this;
 	}
}