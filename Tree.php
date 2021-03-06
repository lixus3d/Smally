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


	protected $_helperNamespace = null;

	/**
	 * Construct the new tree object
	 * @param array  $options Array of $key => $value set as tree properties, if a 'children' key is given, assume that's it's a sub array of Tree to construct
	 * @param \Smally\Tree $parent  A Smally\Tree parent object for back reference
	 */
	public function __construct($options=array(),\Smally\Tree $parent=null){

		$this->setHelperNamespace( (string)\Smally\Application::getInstance()->getConfig()->smally->tree->namespace->helper?:'\\Smally\\' );

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
			$method = 'set'.ucfirst($key);
			if(method_exists($this, $method)){
				$this->$method($opt);
			}else{
				$this->{$key} = $opt;
			}
		}
		foreach($options as $key => $opt){		
			$method = 'onSet'.ucfirst($key);
			if(method_exists($this, $method)){				
				$this->$method();
			}	
		}
		return $this;
	}


	/**
	 * Define the decorator namespace to use for the form
	 * @param string $ns namespace
	 */
	public function setHelperNamespace($ns){
		$this->_helperNamespace = $ns;
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
		if($child instanceof static){
			$this->_children[] = $child;
		}else{
			$this->_children[] = new static($child,$this);
		}
		return $this;
	}

	/**
	 * Add a child to the begin of the tree ( a sub Tree object )
	 * @param  $child Child array
	 */
	public function unshiftChild($child){
		if($child instanceof static){
			array_unshift($this->_children,$child);
		}else{
			array_unshift($this->_children, new static($child,$this) );
		}
		return $this;
	}

	/**
	 * Define an attribute of the tree element when rendered
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Tree
	 */
	public function setAttribute($attribute,$value,$type='_attributes',$propagation=false){
		switch($attribute){
			case 'class':
				if(!isset($this->{$type}[$attribute])) $this->{$type}[$attribute] = array();
				if(is_array($value)){
					$this->{$type}[$attribute] = array_merge($this->{$type}[$attribute],$value);
				}else{
					$this->{$type}[$attribute][] = $value;
				}
			break;
			default:
				$this->{$type}[$attribute] = $value;
			break;
		}
		if($propagation && $parent = $this->getParent()){
			$parent->setAttribute($attribute,$value,$type,$propagation);
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
	 * Return the path of the tree ( loop thru parents element)
	 * @return array Array of \Smally\Tree
	 */
	public function getPath($withMe=true,$withRoot=false){

		$path = array();

		if($withMe) $path[] = $this;

		$obj = $this;
		while($parent = $obj->getParent()){
			$path[] = $parent;
			$obj = $parent;
		}
		if(!$withRoot) array_pop($path);

		return array_reverse($path);
	}

	/**
	 * Return a menu generator object
	 * @return \Smally\Helper\Menu
	 */
	public function getMenu(){

		$type = 'Helper\\Menu';

		$name = $this->_helperNamespace.$type; // Try the form namespace
		if(!class_exists($name)){
			$name = '\\Smally\\'.$type; // try the form default namespace
		}
		if(!class_exists($name)){
			throw new Exception('Helper type unavailable : '.$type);
		}

		$menu = new $name();
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
