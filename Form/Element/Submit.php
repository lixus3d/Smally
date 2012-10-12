<?php

namespace Smally\Form\Element;

class Submit extends AbstractElement{

	protected $_type = 'submit';

	protected $_attributes = array(
		'class' => array('btn','btn-primary'),
	);

	/**
	 * There isn't logic to populate a Submit
	 * @param  string $value Irrevelant for submit element
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function populateValue($value){
		return $this;
	}
}