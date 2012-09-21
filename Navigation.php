<?php

namespace Smally;

/**
 * Default navigation object
 */
class Navigation {

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
	 * Init navigation trees from $trees
	 * @param  array $trees Array of navigation trees , usually a tree by global menu (header, footer, column )
	 * @return \Smally\Navigation
	 */
	public function initNavigation($trees){
		foreach($trees as $treeName => $tree){
			$this->_trees[$treeName] = new NavigationTree(array('children'=>$tree),null,$this);
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
	 * Return a precise navigation tree , usually name of a global menu (header, footer, column)
	 * @param  string $treeName The tree name you want to get
	 * @return \Smally\NavigationTree
	 */
	public function getTree($treeName){
		if(isset($this->_trees[$treeName])) return $this->_trees[$treeName];
		return null;
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