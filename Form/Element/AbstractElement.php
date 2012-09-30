<?php

namespace Smally\Form\Element;

abstract class AbstractElement implements InterfaceElement {

	protected $_name 		= '';
	protected $_value 		= null;
	protected $_type 		= null;
	protected $_label		= null;
	protected $_comment 	= null; // shown near the label
	protected $_help 		= null; // shown near the field
	protected $_error 		= null;

	protected $_form		= null; // reverse reference to the form associate

	protected $_decorator	= 'input'; // input default decorator

	/**
	 * Construct a new form Element (field)
	 * @param array $options An associative array where key = property name
	 */
	public function __construct(array $options=array()){
		if($options){
			foreach($options as $key => $option){
				if(method_exists($this, 'set'.ucfirst($key))){
					$this->{'set'.ucfirst($key)}($option);
				}else throw new Exception('invalid element construct option');
			}
		}
	}

	/**
	 * Define the Decorator name that will be used to render the field itself
	 * @param string $decorator The decorator class name
	 */
	public function setDecorator($decorator){
		$this->_decorator = $decorator;
		return $this;
	}

	/**
	 * Set the name of the element, usually use in name attribute of the input
	 * @param string $name Name of the input
	 */
	public function setName($name){
		$this->_name = $name;
		return $this;
	}

	/**
	 * Set the value of the element, usually use in value attribute of the input
	 * @param string $name Name of the input
	 */
	public function setValue($value){
		$this->_value = $value;
		return $this;
	}

	/**
	 * Set the type of the element
	 * @param string $type Type of the element
	 */
	public function setType($type){
		$this->_type = $type;
		return $this;
	}

	/**
	 * Set the field label used in render of the element
	 * @param string $label The label shown near the field
	 */
	public function setLabel($label){
		$this->_label = $label;
		return $this;
	}

	/**
	 * Set the back reference to the element's form
	 * @param \Smally\Form $form The back referenced form
	 */
	public function setForm(\Smally\Form $form){
		$this->_form = $form;
		return $this;
	}

	/**
	 * Set the field comment
	 * @param string $comment A comment string
	 */
	public function setComment($comment){
		$this->_comment = $comment;
		return $this;
	}

	/**
	 * Set the field help
	 * @param string $help A help string
	 */
	public function setHelp($help){
		$this->_help = $help;
		return $this;
	}

	/**
	 * Set the error text of the field
	 * @param string $error Error or errors of the field
	 */
	public function setError($error){
		$this->_error = $error;
		return $this;
	}

	/**
	 * Get the associate form
	 * @return \Smally\Form associate form
	 */
	public function getForm(){
		return $this->_form;
	}

	/**
	 * Get the decorator name to use for the field itself
	 * @return string
	 */
	public function getDecorator(){
		return $this->_decorator;
	}

	/**
	 * Get the element name
	 * @return string
	 */
	public function getName(){
		if($prefix = $this->getForm()->getNamePrefix()){
			return $prefix.'['.$this->_name.']';
		}
		return $this->_name;
	}

	/**
	 * Get the element value
	 * @return string
	 */
	public function getValue(){
		return $this->_value;
	}

	/**
	 * Get the element type
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Get the element label
	 * @return string
	 */
	public function getLabel(){
		return $this->_label;
	}

	/**
	 * Get the element comment
	 * @return string
	 */
	public function getComment(){
		return $this->_comment;
	}

	/**
	 * Get the element help
	 * @return string
	 */
	public function getHelp(){
		return $this->_help;
	}

	/**
	 * Get the element error(s) string
	 * @return string
	 */
	public function getError(){
		return $this->_error;
	}

	/**
	 * Populate the value of the element, must be redefine for checkbox or radio for example to handle checked
	 * @param string $value The value of the element or the value of the option checked/selected
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function populateValue($value){
		return $this->setValue($value);
	}

	/**
	 * Return the rendered version of the field
	 * @return string
	 */
	public function render(){
		return $this->getForm()->getDecorator('element',$this)->render();
	}
}