<?php

namespace Smally;

/**
 * Specific tree element for navigation
 */
class NavigationTree extends Tree{

	protected $_navigation = null;

	/**
	 * Construct the navigation tree element, for the root given the $navigation object
	 * @param array  $options    As the Tree element
	 * @param \Smally\NavigationTree $parent     As the Tree element
	 * @param \Smally\Navigation $navigation Back reference to the navigation object
	 */
	public function __construct($options=array(), \Smally\NavigationTree $parent=null,\Smally\Navigation $navigation=null){
		if($navigation){
			$this->_navigation = $navigation;
		}
		parent::__construct($options,$parent);
	}

	/**
	 * Define the controller path of the given node, add the controller path to the known path of the navigation
	 * @param string $path The controller path we now know
	 * @return \Smally\NavigationTree
	 */
	public function setControllerPath($path){
		$this->controllerPath = $path;
		if($this->getNavigation()){
			$this->getNavigation()->addPath($path,$this);
		}
		return $this;
	}

	/**
	 * Return the back reference to the navigation
	 * @return \Smally\Navigation
	 */
	public function getNavigation(){
		if( !$this->_navigation && $this->getParent() ){
			$this->_navigation = $this->getParent()->getNavigation();
		}
		return $this->_navigation;
	}

	/**
	 * Return the name of a tree element
	 * @return string
	 */
	public function getName(){
		return isset($this->name)&&$this->name?$this->name:'';
	}

	public function getType(){
		return isset($this->type)&&$this->type?$this->type:'page';
	}

	/**
	 * Return the url of a tree element
	 * @return string
	 */
	public function getUrl(){
		if(!isset($this->url)){ // if url is not directly set or not yet compiled
			// Test against url rewriting if existing
			$relativeUrl = $this->getNavigation()->getApplication()->makeControllerUrl($this->getActionPath());
			$this->url = $this->getNavigation()->getApplication()->getBaseUrl($relativeUrl);
		}
		return $this->url;
	}

	/**
	 * Return the controllerPath of the given element if defined
	 * @return string
	 */
	public function getActionPath(){
		return isset($this->controllerPath)&&$this->controllerPath?$this->controllerPath:'';
	}

}