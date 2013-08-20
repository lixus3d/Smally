<?php

namespace Smally;

class Translate {

	protected $_application = null;

	protected $_language = null;

	protected $_loaded = false;
	protected $_defaultLoaded = false;

	/**
	 * Construct the global $Translate object
	 * @param \Smally\Application $application reverse reference to the application
	 * @param array $vars Translate object $vars
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
		$this->_language = $this->getApplication()->getLanguage() ;
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Context
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	public function load(){
		global $globalTranslate;

		$filename = 'i18n/'.$this->_language.'.php';
		if(stream_resolve_include_path($filename)!==false){
			include_once($filename);
			if(isset($translate) && $translate){
				$globalTranslate = $translate;

			}else{
				$this->loadDefaultLanguage();
			}
			$this->_loaded = true;
		}
	}

	public function loadDefaultLanguage(){
		$defaultLanguage = 'fr';

		$filename = 'i18n/'.$defaultLanguage.'.php';

		if(stream_resolve_include_path($filename)!==false){
			include_once($filename);
			if(isset($translate) && $translate){
				$this->_defaultTranslate = $translate;
			}
			$this->_defaultLoaded = true;
		}
	}

	public function getDefaultTranslate($key){
		if(!$this->_defaultLoaded) $this->loadDefaultLanguage();
		if(isset($this->_defaultTranslate[$key])) return $this->_defaultTranslate[$key];
		return null;
	}

	public function translate($key){

		global $globalTranslate;

		if(!$this->_loaded) $this->load();

		if(isset($globalTranslate[$key])) return $globalTranslate[$key];
		elseif($default = $this->getDefaultTranslate($key) ) return $default;
		else return 'Missing translation';
	}

}