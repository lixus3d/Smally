<?php

namespace Smally\Form\Decorator;

class Label extends AbstractDecorator{

	/**
	 * Render the label Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '';

		if($label = $this->getElement()->getLabel()){
			$html .= '<div class="inputLabel">';
			$html = $this->getForm()->getDecorator('comment',$this->_element)->render($html);
			$html .= '<label for="'.$this->getElement()->getName().'">'.$label.'</label>';
			$html .= '</div>';
		}

		return $this->concat($html,$content);
	}
}