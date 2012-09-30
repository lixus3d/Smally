<?php

namespace Smally\Form\Decorator;

class Error extends AbstractDecorator{

	/**
	 * Render the error Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '';
		if($error = $this->getElement()->getError()){
			if(is_array($error)) $error = implode(BR,$error);
			$html .= '<div class="error">'.$error.'</div>';
		}
		return $this->concat($html,$content);
	}
}