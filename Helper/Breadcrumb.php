<?php

namespace Smally\Helper;

class Breadcrumb {

	protected $_tree = null;

	protected $_decoratorNamespace = '\\Smally\\Helper\\Decorator';

	protected $_defaultPath = null;

	protected $_attributes  = array();
	protected $_attributesElement = array();

	/**
	 * Construct the breadcrumb with some options
	 * @param array $options Initialization options
	 */
	public function __construct($options=array()){
		if(is_array($options) && $options){
			$this->init($options);
		}
	}

	/**
	 * Init the breadcrumb with some options
	 * @param  array $options Options to init
	 * @return \Smally\Helper\Breadcrumb
	 */
	public function init($options){
		foreach($options as $key => $opt){
			if(method_exists($this, 'set'.ucfirst($key))){
				$method = 'set'.ucfirst($key);
				$this->$method($opt);
			}else throw new \Smally\Exception('Invalid breadcrumb option given !');
		}
		return $this;
	}

	/**
	 * Set the tree used by the breadcrumb
	 * @param \Smally\Tree $tree A valid smally tree to use
	 */
	public function setTree(\Smally\Tree $tree){
		$this->_tree = $tree;
		return $this;
	}

	/**
	 * Define an attribute of the breadcrumb tag
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Helper\Breadcrumb
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
	 * Define the decorator namespace to use for the breadcrumb
	 * @param string $ns namespace
	 * @return  \Smally\Helper\Breadcrumb
	 */
	public function setDecoratorNamespace($ns){
		$this->_decoratorNamespace = $ns;
		return $this;
	}

	/**
	 * Define a default path to add at the beginning of the breadcrumb
	 * @param array $path A path item like item in the navigation file
	 */
	public function setDefaultPath($path){
		if(is_array($path)){
			$this->_defaultPath = array();
			foreach($path as $item){
				$this->_defaultPath[] = new \Smally\NavigationTree($item,null,$this->getTree()->getNavigation());
			}
		}
		return $this;
	}


	/**
	 * Return the defaultPath for the breadcrumb, default is null
	 * @return array Array of NavigationTree
	 */
	public function getDefaultPath(){
		return $this->_defaultPath;
	}

	/**
	 * Return the breadcrumb tag attributes
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}


	/**
	 * Return the breadcrumb tag attributes
	 * @return array the attributes
	 */
	public function getAttributesElement(){
		return $this->_attributesElement;
	}

	/**
	 * Get a decorator for breadcrumb element
	 * @param  string $type Type/Name of the decorator, usually breadcrumb and breadcrumbElement
	 * @param  mixed $obj  the object to give to the decorator
	 * @return \Smally\Helper\Decorator\AbstractDecorator
	 */
	public function getDecorator($type='breadcrumb',$obj=null){

		if(is_null($obj)) $obj = $this;

		$name = $this->_decoratorNamespace.ucfirst($type); // Try the form namespace
		if(!class_exists($name)){
			$name = '\\Smally\\Helper\\Decorator\\'.ucfirst($type); // try the form default namespace
		}
		if(!class_exists($name)){
			throw new Exception('Decorator type unavailable : '.$type);
		}
		return new $name($obj);
	}

	/**
	 * Return the tree element of the breadcrumb helper
	 * @return \Smally\tree
	 */
	public function getTree(){
		return $this->_tree;
	}

	/**
	 * Render the breadcrumb thru the breadcrumb decorator
	 * @return string
	 */
	public function render(){
		return $this->getDecorator()->render();
	}

}