<?php

namespace Smally\Helper\Decorator;

class MenuElement extends AbstractDecorator {

	protected $_menu = null;

	public function setMenu(\Smally\Helper\Menu $menu){
		$this->_menu = $menu;
		return $this;
	}

	public function getMenu(){
		return $this->_menu;
	}

	/**
	 * Render the menu decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){

		$attributes = $this->getElement()->getAttributes(); // Attributes of the current element , specific id or rel for example
		if($menu = $this->getMenu()){
			$attributes = array_merge($this->getMenu()->getAttributesElement(),$attributes); // Attributes of the menu element, a generic class for
		}

		$html  = '<li'.\Smally\HtmlUtil::toAttributes($attributes).'>';
		$html .= '<a href="'.$this->getElement()->getUrl().'">';
		$html .= '<span>';
		$html .= $this->getElement()->getName();
		$html .= '</span>';
		$html .= '</a>';
		if($this->getElement()->hasChildren()){ // if we have subchildren
			$subMenu = clone $this->getMenu(); // clone the menu generator to copy the parent menu behaviors and attributes
			$subMenu->setTree($this->getElement()); // set the correct tree
			$html .= $subMenu ->render(); // render the sub menu in the actual menuElement
		}
		$html .= '</li>' . NN;

		return $html ;
	}
}