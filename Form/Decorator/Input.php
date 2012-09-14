<?php

namespace Smally\Form\Decorator;

class Input extends AbstractDecorator {

	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'value' => $this->getElement()->getValue(),
			);
		$html = '<div class="input">';
		$html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}
}