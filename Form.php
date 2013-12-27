<?php

namespace Smally;

class Form {

	const METHOD_POST 		= 1;
	const METHOD_GET 		= 2;
	const METHOD_FILE		= 3;

	protected $_application = null;
	protected $_validator = null;

	protected $_decoratorNamespace = null;
	protected $_elementNamespace = null;

	protected $_action 		= '';
	protected $_method 		= self::METHOD_GET;
	protected $_attributes  = array();

	protected $_separator 	= RN;

	protected $_namePrefix  = null;

	protected $_fields 		= array();

	/**
	 * Construct a new form with this $options
	 * @param array $options An associative array where key = property name
	 */
	public function __construct(array $options=array()){

		$this->setDecoratorNamespace( (string)$this->getApplication()->getConfig()->smally->form->namespace->decorator?:'\\Smally\\Form\\Decorator\\' );
		$this->setElementNamespace( (string)$this->getApplication()->getConfig()->smally->form->namespace->element?:'\\Smally\\Form\\Element\\' );

		if($options){
			foreach($options as $key => $option){
				if(method_exists($this, 'set'.ucfirst($key))){
					$this->{'set'.ucfirst($key)}($option);
				}else throw new Exception('invalid form construct option');
			}
		}
		if(method_exists($this, 'init')) $this->init();
	}

	/**
	 * Easy access to render
	 * @return string
	 */
	public function __tostring(){
		return $this->render();
	}

	/**
	 * Method of the form
	 * @param int $method Constant of the method type
	 * @return \Smally\Form
	 */
	public function setMethod($method=self::METHOD_GET){
		$this->_method = $method;
		return $this;
	}

	/**
	 * Define the action of the form
	 * @param string $action Action url
	 * @return \Smally\Form
	 */
	public function setAction($action=''){
		$this->_action = $action;
		return $this;
	}

	/**
	 * Define an attribute of the form tag
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Form
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
	 * Special separator between each element , usually new line
	 * @param string $sep The separator string
	 */
	public function setSeparator($sep){
		$this->_separator = $sep;
		return $this;
	}

	/**
	 * Define the decorator namespace to use for the form
	 * @param string $ns namespace
	 */
	public function setDecoratorNamespace($ns){
		$this->_decoratorNamespace = $ns;
		return $this;
	}

	/**
	 * Define the element namespace to use for the form
	 * @param string $ns namespace
	 */
	public function setElementNamespace($ns){
		$this->_elementNamespace = $ns;
		return $this;
	}

	/**
	 * Define a name prefix for all field , prefix act as an associative array
	 * @param string $prefix The prefix you want
	 */
	public function setNamePrefix($prefix){
		$this->_namePrefix = $prefix;
		return $this;
	}

	/**
	 * Define the validator that will be used on form data
	 * Use to show form validator tricks (required *, mininum chars, etc... )
	 * @param \Smally\Validator $validator The validator you will use to validate form data
	 * @return \Smally\Form
	 */
	public function setValidator(\Smally\Validator $validator){
		$this->_validator = $validator;
		return $this;
	}

	/**
	 * Return the current Application instance
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	/**
	 * Return the method of the form, default return is in string format
	 * @param  boolean $const True to return the constant equivalent ( integer )
	 * @return mixed
	 */
	public function getMethod($const=false){
		if($const) return $this->_method;
		else{
			switch($this->_method){
				default:
				case self::METHOD_GET:
					return 'get';
				case self::METHOD_POST:
					return 'post';
				case self::METHOD_FILE:
					return 'dontrememberyet';
			}
		}
	}

	/**
	 * Return the form action
	 * @return string the form action
	 */
	public function getAction(){
		return $this->_action;
	}

	/**
	 * Return the form tag attributes. It doesn't contains the action and method attribute (for now)
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}

	/**
	 * Return the element separator string
	 * @return string separator
	 */
	public function getSeparator(){
		return $this->_separator;
	}

	/**
	 * Return a decorator type
	 * @param  string $type The type of the decorator to return
	 * @param  object $obj  The object to pass to the decorator
	 * @return \Smally\Form\Decorator\Abt
	 */
	public function getDecorator($type,$obj=null){
		$name = $this->_decoratorNamespace.ucfirst($type); // Try the form namespace
		if(!class_exists($name)){
			$name = '\\Smally\\Form\\Decorator\\'.ucfirst($type); // try the form default namespace
		}
		if(!class_exists($name)){
			throw new Exception('Decorator type unavailable : '.$type);
		}
		return new $name($obj);
	}

	/**
	 * Return the string to use as field prefix ( array format prefix[fieldname] )
	 * @return string
	 */
	public function getNamePrefix(){
		return $this->_namePrefix;
	}

	/**
	 * Return the validator that will be use to validate form datas
	 * @return \Smally\Validator
	 */
	public function getValidator(){
		return $this->_validator;
	}

	/**
	 * Return the fields array of the form
	 * @return array Array of \Smally\Form\Element\AbstractElement
	 */
	public function getFields(){
		return $this->_fields;
	}

	/**
	 * Automatically population field value from context
	 * @return \Smally\Form
	 */
	public function autoPopulateValue(\Smally\Context $context){
		foreach($this->_fields as $fieldName => $fieldObject){
			if($prefix = $this->getNamePrefix()){
				$fieldObject->populateValue($context->{$prefix}->{$fieldName});
			}else{
				$fieldObject->populateValue($context->{$fieldName});
			}
		}
		return $this;
	}

	/**
	 * Fill each field with a value if present in $values
	 * @param  array  $values Array of $fieldName => $value or Iterator
	 * @return \Smally\Form
	 */
	public function populateValue($values=array()){
		if($values){
			$this->populate($values,'populateValue');
		}
		return $this;
	}

	/**
	 * Fill each field with a error if present in $errors
	 * @param  array  $errors Array of $fieldName => $error
	 * @return \Smally\Form
	 */
	public function populateError(array $errors=array()){
		if($errors){
			$this->populate($errors,'setError');
		}
		return $this;
	}

	/**
	 * Execute $method on each field present in $population keys with $population values as param
	 * @param  array  $population Array of $fieldName => $methoParam
	 * @param  string $method     Method to execute
	 * @return \Smally\Form
	 */
	private function populate($population,$method){
		foreach($population as $fieldName => $info){
			if(isset($this->_fields[$fieldName]) && method_exists($this->_fields[$fieldName], $method)){
				$this->_fields[$fieldName]->{$method}($info);
			}
		}
		return $this;
	}

	public function resetValue(){
		foreach($this->_fields as $fieldElement){
			$fieldElement->resetValue();
		}
	}

	/**
	 * Standard way to add a field to the form
	 * @param mixed $fieldType  The field type : text, password, select, etc ... or directly a fieldObject
	 * @param string $fieldName  The field name, use finally in get or post
	 * @param string $fieldLabel The label to use for the field
	 * @param string $fieldValue The actual value of the field
	 */
	public function addField($fieldType,$fieldName=null,$fieldLabel=null,$fieldValue=null,$options=array()){
		if( $fieldType instanceof \Smally\Form\Element\AbstractElement ){
			return $this->addFieldObject($fieldType);
		}else if( $fieldType && $fieldName ){
			$field = $this->newField($fieldType,$fieldName,$fieldLabel,$fieldValue,$options);
			return $this->addFieldObject($field);
		}else{
			throw new \Smally\Exception('Invalid parameters given to Form->addField method !');
		}
	}

	/**
	 * Real method to add a form element object as form field,
	 * @param \Smally\Form\Element\AbstractElement $fieldObject The field object to add
	 * @return \Smally\Form
	 */
	private function addFieldObject(\Smally\Form\Element\AbstractElement $fieldObject){
		$fieldObject->setForm($this);
		$this->_fields[$fieldObject->getName()] = $fieldObject;
		return $this;
	}

	/**
	 * Create a new field object from parameters
	 * @param string $fieldType  The field type : text, password, select, etc ...
	 * @param string $fieldName  The field name, use finally in get or post
	 * @param string $fieldLabel The label to use for the field
	 * @param string $fieldValue The actual value of the field
	 * @return \Smally\Form\Element\Inter
	 */
	public function newField($fieldType,$fieldName,$fieldLabel=null,$fieldValue=null,$options=array()){

		$name = $fieldType;

		if(!class_exists($name)){
			$name = $this->_elementNamespace.ucfirst($fieldType);
		}
		if(!class_exists($name)){
			$name = '\\Smally\\Form\\Element\\'.ucfirst($fieldType);
		}
		if(!class_exists($name)){
			throw new Exception('Element type unavailable : '.$fieldType);
		}

		$options = array_merge($options,array('name'=>$fieldName,'value'=>$fieldValue,'label'=>$fieldLabel));
		$fieldObject = new $name($options);
		return $fieldObject;
	}

	/**
	 * Render the form
	 * @return string the form html content
	 */
	public function render(){
		return $this->getDecorator('form',$this)->render($this->renderFields());
	}

	/**
	 * Render all fields , the form content basically
	 * @return string all fields public
	 */
	public function renderFields(){
		$html = array();
		foreach($this->_fields as $field){
			$html[] = $field->render();
		}
		return implode($this->_separator,$html);
	}


}
