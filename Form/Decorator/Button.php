<?php

namespace Smally\Form\Decorator;

class Button extends AbstractDecorator {

	/**
	 * Render the button Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$attributes = array(
				'name' => $this->getElement()->getName(),
				// 'type' => $this->getElement()->getType(),
				// 'value' => $this->getElement()->getValue(),
			);

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<button '.\Smally\HtmlUtil::toAttributes($attributes).'/>'.$this->getElement()->getValue().'</button>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}

}