<?php

namespace Smally\Form\Decorator;

abstract class AbstractDecorator {

	const APPEND 			= 1;
	const PREPEND 			= 2;

	protected $_element 	= null;
	protected $_mode 		= null;

	/**
	 * Construct a new decorator
	 * @param \Smally\Form\Element\InterfaceElement $element For which element is the decorator intended
	 * @param integer                                $mode   Append decorator after or before actual content
	 */
	public function __construct( \Smally\Form\Element\InterfaceElement $element,$mode=self::APPEND){
		$this->setElement($element);
		$this->setMode($mode);
	}

	/**
	 * Set the element the decorator is linked to
	 * @param \Smally\Form\Element\InterfaceElement $element The element object
	 */
	public function setElement( \Smally\Form\Element\InterfaceElement $element){
		$this->_element = $element;
		return $this;
	}

	/**
	 * Set the concat mode
	 * @param integer $mode Easer APPEND or PREPEND constant
	 */
	public function setMode($mode=self::APPEND){
		$this->_mode = $mode;
		return $this;
	}

	/**
	 * Get the back referenced element object
	 * @return \Smally\Form\Element\InterfaceElement
	 */
	public function getElement(){
		return $this->_element;
	}

	/**
	 * Get the back referenced form thru the element object
	 * @return \Smally\Form
	 */
	public function getForm(){
		return $this->getElement()->getForm();
	}

	/**
	 * Get the concat mode
	 * @return string
	 */
	public function getMode(){
		return $this->_mode;
	}

	/**
	 * Concat the actual content in $content with $generate thru $mode method
	 * @param  string $generate This decorator generate content
	 * @param  string $content  Actual content
	 * @return string
	 */
	public function concat($generate,$content){
		switch($this->getMode()){
			default:
			case self::APPEND:
				return $content.$generate;
			break;
			case self::PREPEND:
				return $generate.$content;
			break;
		}
	}

	/**
	 * Each decorator must defined it's own render method
	 * @param  string $content The existant content string
	 * @return string
	 */
	abstract public function render($content);
}