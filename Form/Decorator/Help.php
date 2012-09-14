<?php

namespace Smally\Form\Decorator;

class Help extends AbstractDecorator{

	/**
	 * Render the help Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '';
		if($help = $this->getElement()->getHelp()) $html .= '<div class="help">'.$help.'</div>';
		return $this->concat($html,$content);
	}
}