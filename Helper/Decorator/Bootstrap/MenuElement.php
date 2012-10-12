<?php

namespace Smally\Helper\Decorator\Bootstrap;

class MenuElement extends \Smally\Helper\Decorator\MenuElement {

	/**
	 * Add specific class and content for dropwon when Twitter Bootstrap
	 * @return null
	 */
	public function onRender(){
		$this->renderChildren();
		$this->getInnerHtml();
		if($this->_renderChildren){
			$this->getElement()->setAttribute('class','dropdown');
			$this->_innerHtml .= ' <b class="caret"></b>';
			$this->_attributesA = array(
					'class' => 'dropdown-toggle',
					'data-toggle' => 'dropdown'
				);
		}
	}

	/**
	 * Add specific class for dropdown to sub menu
	 * @return \Smally\Helper\Menu
	 */
	public function getSubMenu(){
		parent::getSubMenu();
		$this->_subMenu->resetAttribute('class')
					   ->setAttribute('class','dropdown-menu');
		return $this->_subMenu;
	}

}