<?php

namespace Smally\Form\Decorator;

class Element extends AbstractDecorator {

	static protected $_lineNumber = 0;

	/**
	 * Define the actual element number (usually for line odd / even)
	 * @param integer $k the line number
	 */
	public function setLineNumber($k=null){
		if(is_null($k)) $k = ++self::$_lineNumber;
		self::$_lineNumber = $k;
		return $this;
	}

	/**
	 * Get the line number of the decorator
	 * @return integer
	 */
	public function getLineNumber(){
		return self::$_lineNumber;
	}

	/**
	 * Render the content of the Element Decorator
	 * @param  string $content The actual existing content for the field
	 * @return string
	 */
	public function render($content=''){

		if( $this->_element->getType()!=='hidden' ){
			$this->setLineNumber(); // automatic even / odd
			$html 	= '';
			$html 	= $this->getForm()->getDecorator('label',$this->_element)->render($html); // label render
			$html   = $this->getForm()->getDecorator($this->_element->getDecorator(),$this->_element)->render($html); // field render

			$html 	='<div class="line'.($this->getLineNumber()%2?' even':' odd').' '.$this->_element->getType().' '.$this->_element->getName(false).'">'.$html.'</div>'; // wrap them in the Element div
		}else{
			$html   = '<div class="hidden">'.$this->getForm()->getDecorator($this->_element->getDecorator(),$this->_element)->render('').'</div>'; // field render
		}

		return $this->concat($html,$content);
	}
}