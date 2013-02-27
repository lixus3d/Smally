<?php

namespace Smally\Form\Decorator;

class Separator extends AbstractDecorator {

	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '<div class="input separator">';
		$html .= '</div>';

		return $this->concat($html,$content);
	}

}