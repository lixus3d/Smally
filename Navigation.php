<?php

namespace Smally;

/**
 * Default navigation object
 */
class Navigation {

	protected $_application = null;

	protected $_namespace = '\\Smally\\';

	protected $_trees = array();
	protected $_knownControllerPath = array();

	/**
	 * Construct a new Navigation object from a given $path navigation file
	 * @param string $path Path to a navigation configuration file, must contain a $navigation array
	 */
	public function __construct($path=null){
		if($path && is_file($path)){
			require($path);
			if(isset($navigation) && is_array($navigation)) $this->initNavigation($navigation);
		}
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Navigation
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Init navigation trees from $trees
	 * @param  array $trees Array of navigation trees , usually a tree by global menu (header, footer, column )
	 * @return \Smally\Navigation
	 */
	public function initNavigation($trees){
		foreach($trees as $treeName => $tree){
			$this->_trees[$treeName] = $this->getNewTree(array('children'=>$tree),null,$this);
		}
		return $this;
	}

	/**
	 * Add a controller path to the known path of controller
	 * @param string                 $controllerPath The controller path you want to add to know path
	 * @param \Smally\NavigationTree $tree The given tree node for this $controller
	 * @return \Smally\Navigation
	 */
	public function addPath($controllerPath,\Smally\NavigationTree $tree){
		$this->_knownControllerPath[$controllerPath] = $tree;
		return $this;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * Return a precise navigation tree , usually name of a global menu (header, footer, column)
	 * @param  string $treeName The tree name you want to get
	 * @return \Smally\NavigationTree
	 */
	public function getTree($treeName){
		if(isset($this->_trees[$treeName])) return $this->_trees[$treeName];
		return null;
	}

	/**
	 * Get a new tree element , use namespace or fallback default namespace ( use NavigationTree default constructor params)
	 * @return \Smally\NavigationTree;
	 */
	public function getNewTree($options,$parent,$navigation){

		$className = $this->_namespace.'NavigationTree'; // Try the defined namespace
		if(!class_exists($className)){
			$className = '\\Smally\\NavigationTree'; // try the form default namespace
		}
		if(!class_exists($className)){
			throw new Exception('Tree type unavailable : NavigationTree');
		}
		return new $className($options,$parent,$navigation);
	}

	/**
	 * Return a known controller path node if found in know controller path
	 * @param  string $controllerPath The controllerpath you want to get the node
	 * @return \Smally\Navigation
	 */
	public function getControllerNode($controllerPath){
		if(isset($this->_knownControllerPath[$controllerPath])) return $this->_knownControllerPath[$controllerPath];
		return null;
	}

}