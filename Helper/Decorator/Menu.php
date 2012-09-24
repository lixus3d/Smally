<?php

namespace Smally\Helper\Decorator;

class Menu extends AbstractDecorator {

	/**
	 * Return the element (menu) attributes
	 * @return [type] [description]
	 */
	public function getAttributes(){
		return $this->getElement()->getAttributes();
	}

	/**
	 * Render the menu decorator
	 * Usually the only one redefine in another Menu Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$html  = '<ul'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>' . NN;
		$html .= $this->renderChildren();
		$html .= '</ul>' . NN;
		return $this->concat($html,$content);
	}


	/**
	 * Return the render of the current element sub elements
	 * @return string
	 */
	public function renderChildren(){
		$html = '';
		$items = $this->getElement()->getItems();
		foreach($items as $key => $item){
			$html .= $this->getElement()
							->getDecorator('menuElement',$item)
								->setMenu($this->getElement())
								->setElementNumber($key,count($items))
								->render();
		}
		return $html;
	}
}