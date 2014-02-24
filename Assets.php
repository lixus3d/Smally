<?php

namespace Smally;

class Assets {

	static protected $_singleton = null;

	protected $_application = null;

	protected $_assets = array();

	/**
	 * Construct a new Assets object
	 */
	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
		$this->loadMtime();
		$this->loadMinify();
	}

	public function __destruct(){
		$this->writeMtime();
	}

	/**
	 * Set the singleton instance of Assets
	 * @return \Assets
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Return the singleton
	 * @return \Assets
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof Assets){
			new self();
		}
		return self::$_singleton;
	}

	/**
	 * Get the current Smally application
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	///////////////////
	// ASSETS MTIME //
	///////////////////

	/**
	 * Add the mtime of a given asset
	 * @param string $path  The path to the asset (relative)
	 * @param int $mtime The unixtimestamp mtime of the asset ( can be anything but timestamp seems the better solution )
	 * @return \Assets
	 */
	public function addAssetMtime($path,$mtime){
		$this->_assets[$path] = $mtime;
		return $this;
	}

	/**
	 * Get the mtime of a given asset if present in the config file, null if no mtime found
	 * @param  string $path The path to the asset (relative)
	 * @return mixed
	 */
	public function getAssetMtime($path){
		return isset($this->_assets[$path])? $this->_assets[$path] : null;
	}

	/**
	 * Get the config file path
	 * @return string
	 */
	public function getMtimeConfigFilePath(){
		return (string) $this->getApplication()->getConfig()->project->assets->mtimePath?:CONFIG_PATH.'assets_mtime.php';
	}

	/**
	 * Reset the mtime of an asset
	 * @param  string $path The path to the asset (relative)
	 * @return \Assets
	 */
	public function resetAssetMtime($path){
		if(isset($this->_assets[$path])){
			unset($this->_assets[$path]);
		}
		return $this;
	}

	/**
	 * Reset all assets mtime
	 * @return \Assets
	 */
	public function resetAllMtime(){
		$this->_assets = array();
		return $this;
	}

	/**
	 * Load all assets mtime from the config file
	 * @return \Assets
	 */
	public function loadMtime(){
		if($path = $this->getMtimeConfigFilePath()){
			if(file_exists($path)){
				require($path);
				if(isset($assets)) $this->_assets = $assets;
			}
		}
		return $this;
	}

	/**
	 * Write all the assets to the config file
	 * @return \Assets
	 */
	public function writeMtime(){
		$export = var_export($this->_assets,true);

		$data = array();
		$data[] = '<?';
		$data[] = '$assets = ';
		$data[] = $export;
		$data[] = ';';

		file_put_contents($this->getMtimeConfigFilePath(), implode(NN,$data) );
		return $this;
	}

	///////////////////
	// JS MINIFYING //
	///////////////////

	/**
	 * Check weither an asset filepath (js for now) is in minify table or not
	 * @param string $path The path to the asset (relative)
	 * @return boolean true if in minify array
	 */
	public function isMinify($path){
		return in_array($path, $this->_minify);
	}

	/**
	 * Load the minify array from config
	 * @return \Assets
	 */
	public function loadMinify(){
		$minify = $this->getApplication()->getConfig()->project->minify->js->toArray();
		if($minify){
			$this->_minify = $minify;
		}
		return $this;
	}

}