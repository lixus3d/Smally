<?php

namespace Smally\Helper\Decorator;

class PagingElement extends AbstractDecorator {

	protected $_classActive = 'active';
	protected $_classAlpha 	= 'alpha';
	protected $_classOmega  = 'omega';

	protected $_paging = null;

	/**
	 * Define the actual element number (usually for element alpha / omega)
	 * @param integer $k the element number
	 * @return \Smally\Helper\Decorator\PagingElement
	 */
	public function setElementNumber($k=null,$total=0){
		if(is_null($k)) $k = ++$this->_elementNumber;
		$this->_elementNumber = $k;
		$this->_elementTotal = $total;
		return $this;
	}

	/**
	 * Define the back reference to the Paging Helper
	 * @param \Smally\Helper\Paging $paging the Paging object
	 * @return \Smally\Helper\Decorator\PagingElement
	 */
	public function setPaging(\Smally\Helper\Paging $paging){
		$this->_paging = $paging;
		return $this;
	}

	/**
	 * Get the item number (position) in the parent object
	 * @return integer
	 */
	public function getElementNumber(){
		return $this->_elementNumber;
	}

	/**
	 * Return the Helper\Paging called for this element
	 * @return \Smally\Helper\Paging
	 */
	public function getPaging(){
		return $this->_paging;
	}

	/**
	 * Return the element attributes, mix between the ones in Menu helper and the one in the element itself
	 * @return array
	 */
	public function getAttributes(){
		$attributes = array();
		if($paging = $this->getPaging()){
			$attributes = array_merge($this->getPaging()->getAttributesElement(),$attributes); // Attributes of the menu element, a generic class for
		}

		// Add the active automatically
		if($application = \Smally\Application::getInstance()){
			$pageNumber = $this->getElement() - 1 ;
			$actualPageNumber = $this->getPaging()->getPage();
			if( $pageNumber == $actualPageNumber ){
				$attributes['class'][] = $this->_classActive;
			}else{
				if($pageNumber == $actualPageNumber-1){
					$application->getMeta()->addHeadTag('link',array('rel'=>'prev','href'=>$this->getPaging()->getUrl($pageNumber+1)),'rel-prev');
				}elseif($pageNumber == $actualPageNumber+1){
					$application->getMeta()->addHeadTag('link',array('rel'=>'next','href'=>$this->getPaging()->getUrl($pageNumber+1)),'rel-next');
				}
			}
		}

		// Add alpha and omega automatically
		if($this->_elementNumber == 1) $attributes['class'][] = $this->_classAlpha;
		if($this->_elementNumber == $this->_elementTotal) $attributes['class'][] = $this->_classOmega;

		return $attributes;
	}

	/**
	 * Render the menu decorator
	 * Usually the only one redefine in another MenuElement Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$html  = '<li'.\Smally\Util::toAttributes($this->getAttributes()).'>';
		$html .= '<a href="'.$this->getPaging()->getUrl($this->getElement()).'">';
		$html .= '<span>';
		$html .= '<span>';
		$html .= $this->getElement();
		$html .= '</span>';
		$html .= '</span>';
		$html .= '</a>';
		$html .= '</li>' . NN;
		return $this->concat($html,$content);
	}

}