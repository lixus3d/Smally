<?php

namespace Smally\Helper;

class Menu {

	protected $_tree = null;
	protected $_parent = null;

	protected $_decoratorNamespace = '\\Smally\\Helper\\Decorator';

	protected $_attributes  = array();
	protected $_attributesElement = array();

	/**
	 * Construct the menu with some options
	 * @param array $options Initialization options
	 */
	public function __construct($options=array()){
		if(is_array($options) && $options){
			$this->init($options);
		}
	}

	/**
	 * Init the menu with some options
	 * @param  array $options Options to init
	 * @return \Smally\Helper\Menu
	 */
	public function init($options){
		foreach($options as $key => $opt){
			if(method_exists($this, 'set'.ucfirst($key))){
				$method = 'set'.ucfirst($key);
				$this->$method($opt);
			}else throw new \Smally\Exception('Invalid menu option given !');
		}
		return $this;
	}

	/**
	 * Set the tree used by the menu
	 * @param \Smally\Tree $tree A valid smally tree to use
	 */
	public function setTree(\Smally\Tree $tree){
		$this->_tree = $tree;
		return $this;
	}

	public function setParent(\Smally\Tree $parent){
		$this->_parent = $parent;
		return $this;
	}

	/**
	 * Define an attribute of the menu tag
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Helper\Menu
	 */
	public function setAttribute($attribute,$value,$type='_attributes'){
		switch($attribute){
			case 'class':
				if(!isset($this->{$type}[$attribute])) $this->{$type}[$attribute] = array();
				$this->{$type}[$attribute][] = $value;
			break;
			default:
				$this->{$type}[$attribute] = $value;
			break;
		}
		return $this;
	}

	/**
	 * Set an attribute of a list element
	 * @param string $attribute The attribute name
	 * @param string $value     The value of the attribute
	 */
	public function setAttributeElement($attribute,$value){
		return $this->setAttribute($attribute,$value,'_attributesElement');
	}


	/**
	 * Define the decorator namespace to use for the menu
	 * @param string $ns namespace
	 * @return  \Smally\Helper\Menu
	 */
	public function setDecoratorNamespace($ns){
		$this->_decoratorNamespace = $ns;
		return $this;
	}


	/**
	 * Return the menu tag attributes
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}


	/**
	 * Return the menu tag attributes
	 * @return array the attributes
	 */
	public function getAttributesElement(){
		return $this->_attributesElement;
	}

	/**
	 * Get a decorator for menu element
	 * @param  string $type Type/Name of the decorator, usually menu and menuElement
	 * @param  mixed $obj  the object to give to the decorator
	 * @return \Smally\Helper\Decorator\AbstractDecorator
	 */
	public function getDecorator($type,$obj=null){
		$name = $this->_decoratorNamespace.ucfirst($type); // Try the current namespace
		if(!class_exists($name)){
			$name = '\\Smally\\Helper\\Decorator\\'.ucfirst($type); // try the helper default namespace
		}
		/*if(!class_exists($name)){
			throw new Exception('Decorator type unavailable : '.$type);
		}*/
		return new $name($obj);
	}

	public function getTree(){
		return $this->_tree;
	}

	public function getParent(){
		return $this->_parent;
	}

	/**
	 * Return the menu items , tree children
	 * @return array Array of \Smally\Tree
	 */
	public function getItems(){
		return $this->getTree()->getChildren();
	}

	/**
	 * Render the menu thru the menu decorator
	 * @return string
	 */
	public function render(){
		return $this->getDecorator('menu',$this)->render();
	}

}