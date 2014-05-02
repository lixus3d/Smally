<?php

namespace Smally;

class Translate {

	protected $_application = null;

	protected $_language = null;
	protected $_defaultLanguage = null;

	protected $_translate = null;
	protected $_defaultTranslate = null;

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

	/**
	 * Return the actual wanted language (2 letters style)
	 * @return string
	 */
	public function getLanguage(){
		return $this->_language;
	}

	/**
	 * Return the default fallback language (2 letters style)
	 * @return string
	 */
	public function getDefaultLanguage(){
		if(is_null($this->_defaultLanguage)){
			$this->_defaultLanguage = (string)$this->getApplication()->getConfig()->smally->defaultLanguage?:'fr';
		}
		return $this->_defaultLanguage;
	}

	public function getLoadPaths(){
		return array(
			LIBRARY_PATH.'Smally/',
			ROOT_PATH,
			MODULE_PATH.$this->getApplication()->getSiteNamespace().'/',
			);
	}

	/**
	 * Load translation from smally and then from project, them from site namespace to allow overwrite
	 * @return \Smally\Translate
	 */
	public function load(){

		$language = $this->getLanguage();
		$this->_translate = array();

		$filename = 'i18n/'.$language.'.php';

		foreach($this->getLoadPaths() as $path){
			$filePath = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$path.$filename);
			if(file_exists($filePath)){
				include_once($filePath);
				if(isset($translate)&&is_array($translate)){
					$this->_translate = array_merge($this->_translate,$translate);
				}
			}
		}

	}

	/**
	 * Load fallback translate. Usually loaded on demand if key not find in actual language
	 * @return \Smally\Translate
	 */
	public function loadDefault(){

		$defaultLanguage = $this->getDefaultLanguage();
		$this->_defaultTranslate = array();

		$filename = 'i18n/'.$defaultLanguage.'.php';

		foreach($this->getLoadPaths() as $path){
			$filePath = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$path.$filename);
			if(file_exists($filePath)){
				include_once($filePath);
				if(isset($translate)&&is_array($translate)){
					$this->_defaultTranslate = array_merge($this->_defaultTranslate,$translate);
				}
			}
		}
	}

	/**
	 * Return the translation of a key if existing. Load the translate if not yet loaded automatically
	 * @param  string $key The translate key name you want the actual language translation
	 * @return string
	 */
	public function getTranslation($key){
		if(is_null($this->_translate)) $this->load();
		return isset($this->_translate[$key]) ? $this->_translate[$key] : null;
	}

	/**
	 * Return the default translation of a key if existing. Load the default translate if not yet loaded automatically
	 * @param  string $key The translate key name you want the default language translation
	 * @return string
	 */
	public function getDefaultTranslation($key){
		if(is_null($this->_defaultTranslate)) $this->loadDefault();
		return isset($this->_defaultTranslate[$key]) ? $this->_defaultTranslate[$key] : null;
	}

	/**
	 * Translate a key
	 * @param  string $key The translate key name you want to translate
	 * @return tring
	 */
	public function translate($key){
		if( !($translation = $this->getTranslation($key)) ){
			if( !($translation = $this->getDefaultTranslation($key)) ){
				$translation = 'Missing translation';
			}
		}
		return $translation;
	}

}