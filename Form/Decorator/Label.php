<?php

namespace Smally\Form\Decorator;

class Label extends AbstractDecorator{

	/**
	 * Render the label Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '<div class="label">';
		$html = $this->getForm()->getDecorator('comment',$this->_element)->render($html);
		$html .= '<label for="'.$this->getElement()->getName().'">'.$this->getElement()->getLabel().'</label>';
		$html .= '</div>';

		return $this->concat($html,$content);
	}
}