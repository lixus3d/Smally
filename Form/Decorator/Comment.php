<?php

namespace Smally\Form\Decorator;

class Comment extends AbstractDecorator{

	/**
	 * Render the Comment decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '';
		if($comment = $this->getElement()->getComment()){
			$html .= '<div class="comment">'.$comment.'</div>';
		}
		return $this->concat($html,$content);
	}
}