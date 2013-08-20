<?php

namespace Smally\Form\Element;

class Button extends AbstractElement{

	protected $_type = 'button';
	protected $_decorator = 'button';

	protected $_attributes = array(
		'class' => array('btn','btn-primary'),
	);

	/**
	 * There isn't logic to populate a Button
	 * @param  string $value Irrevelant for submit element
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function populateValue($value){
		return $this;
	}
}