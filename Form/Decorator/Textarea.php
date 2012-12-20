<?php

namespace Smally\Form\Decorator;

class Textarea extends AbstractDecorator {

	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$attributes = array(
				'name' => $this->getElement()->getName(),
			);
		$value = $this->getElement()->getValue();

		$placeholder = $this->getElement()->getPlaceholder();
		$default = $this->getElement()->getDefault();

		if($placeholder !== '') $attributes['placeholder'] = $placeholder;
		if($default !== '' && $value == '') $value = $default;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<textarea '.\Smally\HtmlUtil::toAttributes($attributes).'>'. _h($value).'</textarea>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}
}