<?php

namespace Smally\Helper\Decorator;

class MenuElement extends AbstractDecorator {

	protected $_elementNumber = null;
	protected $_elementTotal = null;

	protected $_menu = null;
	protected $_subMenu = null;

	protected $_classActive = 'active';
	protected $_classAlpha 	= 'alpha';
	protected $_classOmega  = 'omega';

	protected $_innerHtml = null;
	protected $_renderChildren = null;

	protected $_attributesA = array();

	/**
	 * Define the back reference to the Menu Helper
	 * @param \Smally\Helper\Menu $menu the Menu
	 * @return \Smally\Helper\Decorator\MenuElement
	 */
	public function setMenu(\Smally\Helper\Menu $menu){
		$this->_menu = $menu;
		return $this;
	}

	/**
	 * Define the actual element number (usually for element alpha / omega)
	 * @param integer $k the element number
	 */
	public function setElementNumber($k=null,$total=0){
		if(is_null($k)) $k = ++$this->_elementNumber;
		$this->_elementNumber = $k;
		$this->_elementTotal = $total;
		return $this;
	}

	/**
	 * Return the Helper\Menu called for this element
	 * @return \Smally\Helper\Menu
	 */
	public function getMenu(){
		return $this->_menu;
	}
		/**
	 * Get the line number of the decorator
	 * @return integer
	 */
	public function getElementNumber(){
		return self::$_elementNumber;
	}

	/**
	 * Return the element attributes, mix between the ones in Menu helper and the one in the element itself
	 * @return array
	 */
	public function getAttributes(){
		$attributes = $this->getElement()->getAttributes(); // Attributes of the current element , specific id or rel for example


		if($menu = $this->getMenu()){
			$attributes = array_merge($this->getMenu()->getAttributesElement(),$attributes); // Attributes of the menu element, a generic class for
		}

		// TODO : Do a x logic function
		// Add the active automatically
		if($application = \Smally\Application::getInstance()){

			// try the url approach
			$actualUrl = $application->getRooter()->getActualUrl();
			if($this->getElement()->getUrl() == $actualUrl && !$this->getElement()->isShortcut() ){
				$attributes['class'][] = $this->_classActive;
				// Add the active class to the parent of the current menu
				if($parent = $this->getMenu()->getParent()){
					$parent->setAttribute('class',$this->_classActive);
				}
			}

			// $actualUrl = $application->getRooter()->getActionPath();
			// if($this->getElement()->getActionPath() == $actualUrl){
			// 	$attributes['class'][] = $this->_classActive;
			// 	// Add the active class to the parent of the current menu
			// 	if($parent = $this->getMenu()->getParent()){
			// 		$parent->setAttribute('class',$this->_classActive);
			// 	}

			// }
		}

		// Add alpha and omega automatically
		if($this->_elementNumber === 0) $attributes['class'][] = $this->_classAlpha;
		if($this->_elementNumber === ($this->_elementTotal-1)) $attributes['class'][] = $this->_classOmega;

		// Add element specific class
		if(isset($this->getElement()->class) && $class = $this->getElement()->class){
			$attributes['class'][] = $class;
		}

		return $attributes;
	}

	public function getInnerHtml(){
		if(is_null($this->_innerHtml)){
			$this->_innerHtml = $this->getElement()->getName();
		}
		return $this->_innerHtml;
	}

	/**
	 * Render the menu decorator
	 * Usually the only one redefine in another MenuElement Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){

		if(method_exists($this, 'onRender')){
			$this->{'onRender'}();
		}

		// Switch on element type
		switch($this->getElement()->getType()){
			case 'separator':
				$this->getElement()->setAttribute('class','divider');
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<span>';
				$html .= '<span>';
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</li>' . NN;
			break;
			case 'header':
				$this->getElement()->setAttribute('class','nav-header');
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<span>';
				$html .= '<span>';
				$html .= $this->getInnerHtml();
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</li>' . NN;
			break;
			default:
			case 'page':
				// we render children before to adapt attributes if necessary (active class for example, or hasChildren)
				$children = $this->renderChildren();
				$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
				$html .= '<a href="'.$this->getElement()->getUrl().'" '.\Smally\HtmlUtil::toAttributes($this->_attributesA).'>';
				if($icon = $this->getElement()->getIcon()){
					$html .= $icon;
				}
				$html .= '<span>';
				$html .= '<span>';
				$html .= $this->getInnerHtml();
				$html .= '</span>';
				$html .= '</span>';
				$html .= '</a>';
				$html .= $children;
				$html .= '</li>' . NN;
			break;
		}
		return $this->concat($html,$content);
	}

	/**
	 * Return the render of the element sub elements
	 * @return string
	 */
	public function renderChildren(){

		if(is_null($this->_renderChildren)){

			$this->_renderChildren = '';

			// if we are in the level to render
			$renderLevel = $this->getMenu()->getRenderLevel();
			$level = $this->getMenu()->getLevel();

			if(is_null($renderLevel) || ($level+1 < $renderLevel)){
				if($this->getElement()->hasChildren()){ // if we have subchildren
					$this->_renderChildren = $this->getSubMenu()->render(); // render the sub menu in the actual menuElement
				}
			}
		}
		return $this->_renderChildren;
	}

	/**
	 * Return the subMenu Helper Menu object for children of the current element
	 * @return \Smally\Helper\Menu
	 */
	public function getSubMenu(){
		if(is_null($this->_subMenu)){
			$this->_subMenu = clone $this->getMenu(); // clone the menu generator to copy the parent menu behaviors and attributes
			$this->_subMenu->setLevel($this->getMenu()->getLevel()+1)
							->setTree($this->getElement()) // set the correct tree
							->setParent($this->getElement())
							;
		}
		return $this->_subMenu;
	}
}