<?php

namespace Smally\Helper\Decorator;

/**
 * Almost the same as the MenuElement
 */

class BreadcrumbElement extends AbstractDecorator {

	protected $_elementNumber = null;
	protected $_elementTotal = null;

	protected $_breadcrumb = null;

	protected $_classActive = 'active';
	protected $_classAlpha 	= 'alpha';
	protected $_classOmega  = 'omega';

	/**
	 * Define the back reference to the Breadcrumb Helper
	 * @param \Smally\Helper\Breadcrumb $breadcrumb the breadcrumb
	 * @return \Smally\Helper\Decorator\BreadcrumbElement
	 */
	public function setBreadcrumb(\Smally\Helper\Breadcrumb $breadcrumb){
		$this->_breadcrumb = $breadcrumb;
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
	 * Return the Helper\Breadcrumb called for this element
	 * @return \Smally\Helper\Breadcrumb
	 */
	public function getBreadcrumb(){
		return $this->_breadcrumb;
	}

	/**
	 * Get the line number of the decorator
	 * @return integer
	 */
	public function getElementNumber(){
		return self::$_elementNumber;
	}

	/**
	 * Return the element attributes, mix between the ones in Breadcrumb helper and the one in the element itself
	 * @return array
	 */
	public function getAttributes(){
		$attributes = $this->getElement()->getAttributes(); // Attributes of the current element , specific id or rel for example
		if($breadcrumb = $this->getBreadcrumb()){
			$attributes = array_merge($this->getBreadcrumb()->getAttributesElement(),$attributes); // Attributes of the breadcrumb element, a generic class for
		}

		// Add the active automatically
		if($application = \Smally\Application::getInstance()){
			$actualUrl = $application->getRooter()->getActualUrl();
			if($this->getElement()->getUrl() == $actualUrl){
				$attributes['class'][] = $this->_classActive;
			}
		}

		// Add alpha and omega automatically
		if($this->_elementNumber === 0) $attributes['class'][] = $this->_classAlpha;
		if($this->_elementNumber === ($this->_elementTotal-1)) $attributes['class'][] = $this->_classOmega;

		return $attributes;
	}

	/**
	 * Render the breadcrumb decorator
	 * Usually the only one redefine in another BreadcrumbElement Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$html  = '<li'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>';
		$html .= '<a href="'.$this->getElement()->getUrl().'">';
		$html .= '<span>';
		$html .= '<span>';
		$html .= $this->getElement()->getName();
		$html .= '</span>';
		$html .= '</span>';
		$html .= '</a>';
		$html .= '</li>' . NN;
		return $this->concat($html,$content);
	}
}