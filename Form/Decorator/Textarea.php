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

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<textarea '.\Smally\HtmlUtil::toAttributes($attributes).'>'. _h($this->getElement()->getValue()).'</textarea>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}
}