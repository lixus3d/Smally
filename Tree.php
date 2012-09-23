<?php

namespace Smally;

/**
 * Generic tree object
 */
class Tree {

	protected $_level = null;
	protected $_parent = null;
	protected $_children = array();

	protected $_attributes = array();

	/**
	 * Construct the new tree object
	 * @param array  $options Array of $key => $value set as tree properties, if a 'children' key is given, assume that's it's a sub array of Tree to construct
	 * @param \Smally\Tree $parent  A Smally\Tree parent object for back reference
	 */
	public function __construct($options=array(),\Smally\Tree $parent=null){
		if(!is_null($parent)){
			$this->setParent($parent);
		}
		if(is_array($options) && $options){
			$this->init($options);
		}
	}

	/**
	 * Init a tree object
	 * @param  array $options Array of $key => $value set as tree properties, if a 'children' key is given, assume that's it's a sub array of Tree to construct
	 * @return \Smally\Tree
	 */
	public function init($options){
		foreach($options as $key => $opt){
			if(method_exists($this, 'set'.ucfirst($key))){
				$method = 'set'.ucfirst($key);
				$this->$method($opt);
			}else{
				$this->{$key} = $opt;
			}
		}
		return $this;
	}

	/**
	 * Define the tree parent , must be another \Smally\Tree
	 * @param \Smally\Tree $parent the parent object
	 * @return \Smally\Tree
	 */
	public function setParent(\Smally\Tree $parent){
		$this->_parent = $parent;
		return $this;
	}

	/**
	 * Define the level of the tree element, automatically computed if not given
	 * @param int $level the level of the element
	 * @return \Smally\Tree
	 */
	public function setLevel($level){
		$this->_level = (int) $level;
		return $this;
	}

	/**
	 * Define the children of the element
	 * @param array $children Array of sub elements , each of them will be created as a \Smally\Tree element
	 * @return \Smally\Tree
	 */
	public function setChildren($children){
		foreach($children as $child){
			$this->addChild($child);
		}
		return $this;
	}

	/**
	 * Add a child to the tree ( a sub Tree object )
	 * @param  $child Child array
	 */
	public function addChild($child){
		$this->_children[] = new static($child,$this);
		return $this;
	}

	/**
	 * Define an attribute of the tree element when rendered
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Tree
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

	public function hasChildren(){
		return !empty($this->_children);
	}
	/**
	 * Get the parent object of the current Tree
	 * @return \Smally\Tree Can be null if "root" element
	 */
	public function getParent(){
		return $this->_parent;
	}

	/**
	 * Get the tree children array ( array of sub Tree )
	 * @return array
	 */
	public function getChildren(){
		return $this->_children;
	}

	/**
	 * Return the level of the given Tree , computed from parent or gett
	 * @return int
	 */
	public function getLevel(){
		if(is_null($this->_level)){
			if(!is_null($this->getParent())){
				$this->_level = $this->getParent()->getLevel() + 1 ;
			} else $this->_level = 0;
		}
		return $this->_level;
	}

	/**
	 * Return the tree tag attributes when rendered
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}

	/**
	 * Return a menu generator object
	 * @return \Smally\Helper\Menu
	 */
	public function getMenu(){
		$menu = new Helper\Menu();
		$menu->setTree($this);
		return $menu;
	}

	/**
	 * Return a path generator object
	 * @return \Smally\Helper\Breadcrumb
	 */
	public function getBreadcrumb(){
		$breadcrumb = new Helper\Breadcrumb();
		$breadcrumb->setTree($this);
		return $breadcrumb;
	}

}
