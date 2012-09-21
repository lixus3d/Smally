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
		$this->getNavigation()->addPath($path,$this);
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

}