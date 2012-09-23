<?php

namespace Smally\Helper\Decorator;

abstract class AbstractDecorator {

	const APPEND 			= 1;
	const PREPEND 			= 2;

	protected $_element 	= null;
	protected $_mode 		= null;

	/**
	 * Construct a new decorator
	 * @param object  $element For which element is the decorator intended
	 * @param integer $mode   Append decorator after or before actual content
	 */
	public function __construct($element=null,$mode=self::APPEND){
		$this->setElement($element);
		$this->setMode($mode);
	}

	/**
	 * Set the element the decorator is linked to
	 * @param object $element The element object
	 * @return \Smally\Helper\Decorator\AbstractDecorator
	 */
	public function setElement($element){
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
	 * @return object
	 */
	public function getElement(){
		return $this->_element;
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