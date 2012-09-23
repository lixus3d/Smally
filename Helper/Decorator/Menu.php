<?php

namespace Smally\Helper\Decorator;

class Menu extends AbstractDecorator {

	/**
	 * Render the menu decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$attributes = $this->getElement()->getAttributes();
		$menu = $this->getElement();

		$html = '<ul'.\Smally\HtmlUtil::toAttributes($attributes).'>' . NN;
		foreach($this->getElement()->getItems() as $key => $item){
			$html .= $this->getElement()->getDecorator('menuElement',$item)->setMenu($menu)->render() . NN;
		}
		$html .= '</ul>' . NN;
		return $html ;
	}
}