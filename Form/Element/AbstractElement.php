<?php

namespace Smally\Form\Element;

abstract class AbstractElement implements InterfaceElement {

	protected $_name 		= '';
	protected $_value 		= null;
	protected $_placeholder = null;
	protected $_default		= null;
	protected $_type 		= null;
	protected $_label		= null;
	protected $_comment 	= null; // shown near the label
	protected $_help 		= null; // shown near the field
	protected $_error 		= null;

	protected $_form		= null; // reverse reference to the form associate

	protected $_decorator	= 'input'; // input default decorator

	protected $_attributes  = array();

	/**
	 * Construct a new form Element (field)
	 * @param array $options An associative array where key = property name
	 */
	public function __construct(array $options=array()){
		if($options){
			foreach($options as $key => $option){
				if(method_exists($this, 'set'.ucfirst($key))){
					$this->{'set'.ucfirst($key)}($option);
				}else throw new \Exception('invalid element construct option');
			}
		}
		if(method_exists($this, 'init')){
			$this->init();
		}
	}

	/**
	 * Define the Decorator name that will be used to render the field itself
	 * @param string $decorator The decorator class name
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setDecorator($decorator){
		$this->_decorator = $decorator;
		return $this;
	}

	/**
	 * Set the name of the element, usually use in name attribute of the input
	 * @param string $name Name of the input
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setName($name){
		$this->_name = $name;
		return $this;
	}

	/**
	 * Set the value of the element, usually use in value attribute of the input
	 * @param string $name Name of the input
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setValue($value){
		if($value instanceof \Smally\ContextStdClass){
			if($value->isEmpty()){
				$value = null;
			}else{
				$value = $value->toArray();
			}
		}
		$this->_value = $value;
		return $this;
	}

	/**
	 * Set placeholder text help to show in the field
	 * @param string $placeholder The placeholder you want
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setPlaceholder($placeholder){
		$this->_placeholder = $placeholder;
		return $this;
	}

	/**
	 * Set Default value shown in the field if no value present
	 * @param string $default The default value you want to show in the field when no value present
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setDefault($default){
		$this->_default = $default;
		return $this;
	}

	/**
	 * Set the type of the element
	 * @param string $type Type of the element
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setType($type){
		$this->_type = $type;
		return $this;
	}

	/**
	 * Set the field label used in render of the element
	 * @param string $label The label shown near the field
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setLabel($label){
		$this->_label = $label;
		return $this;
	}

	/**
	 * Set the back reference to the element's form
	 * @param \Smally\Form $form The back referenced form
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setForm(\Smally\Form $form){
		$this->_form = $form;
		return $this;
	}

	/**
	 * Set the field comment
	 * @param string $comment A comment string
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setComment($comment){
		$this->_comment = $comment;
		return $this;
	}

	/**
	 * Set the field help
	 * @param string $help A help string
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setHelp($help){
		$this->_help = $help;
		return $this;
	}

	/**
	 * Set the error text of the field
	 * @param string $error Error or errors of the field
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setError($error){
		$this->_error = $error;
		return $this;
	}

	/**
	 * Define an attribute of the input tag
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function setAttribute($attribute,$value){
		switch($attribute){
			case 'class':
				if(!isset($this->_attributes[$attribute])) $this->_attributes[$attribute] = array();
				$this->_attributes[$attribute][] = $value;
			break;
			default:
				$this->_attributes[$attribute] = $value;
			break;
		}
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
	public function getName($withPrefix=true){
		if($withPrefix && $prefix = $this->getForm()->getNamePrefix()){
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
	 * Get the placeholder
	 * @return string
	 */
	public function getPlaceholder(){
		return $this->_placeholder;
	}

	/**
	 * Get the default value
	 * @return string
	 */
	public function getDefault(){
		return $this->_default;
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
	 * Return the input tag attributes
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
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
	 * Reset value of the element
	 * @return \Smally\Form\Element\AbstractElement
	 */
	public function resetValue(){
		$this->_value = null;
		return $this;
	}

	/**
	 * Return the rendered version of the field
	 * @return string
	 */
	public function render(){
		return $this->getForm()->getDecorator('element',$this)->render();
	}
}