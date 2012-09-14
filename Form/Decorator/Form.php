<?php

namespace Smally\Form\Decorator;

class Form{

	protected $_form = null;

	/**
	 * Create a new form decorator
	 * @param \Smally\Form $form The form to decorate
	 */
	public function __construct( \Smally\Form $form){
		$this->_form = $form;
	}

	/**
	 * Render the form decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$attributes = array('method'=>$this->_form->getMethod(),'action'=>$this->_form->getAction());
		$attributes = array_merge($attributes,$this->_form->getAttributes());

		return '<form'.\Smally\HtmlUtil::toAttributes($attributes).'>'
				.$this->_form->getSeparator()
				.$content
				.$this->_form->getSeparator()
				.'</form>';
	}
}