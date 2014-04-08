<?php

namespace Smally;

class SCache {

	static protected $_singleton = null;

	protected $_connector = null;
	protected $_ttl = null;

	public function __construct(){
		$this->_ttl = (int)(string)\Smally\Application::getInstance()->getConfig()->smally->cache->ttl?:36000;
	}

	/**
	 * Get the current cache connector, default is APC
	 * @return \Smally\SCache\InterfaceConnector
	 */
	public function getConnector(){
		if(is_null($this->_connector)){
			$connectorName = (string)\Smally\Application::getInstance()->getConfig()->smally->cache->connectorClass?:'Smally\\SCache\\ApcConnector';
			$this->_connector = new $connectorName();
		}
		return $this->_connector;
	}

	/**
	 * Return the current CmsTemplate instance
	 * @return \CmsTemplate\AbstractCmsTemplate
	 */
	static public function getInstance(){
		if( is_null(static::$_singleton) ){
			static::$_singleton = new static();
		}
		return static::$_singleton;
	}

	/**
	 * Return the hash prefix for the current scache (Important to not share keys between multiple projects.)
	 * @return string
	 */
	static public function getHashPrefix(){
		return MD5(ROOT_PATH); // we md5 the ROOT_PATH which is a "good" uniqid to identify a project
	}

	/**
	 * Generate a scache key from params
	 * @param  array $params An array of params that will be the key name (a string is valid but not best)
	 * @return string
	 */
	public function getHashKey($string){
		return self::getHashPrefix().'_'.$string;
	}

	/**
	 * Store a $value in cache system identified by $key
	 * @param string  $key   the key to access/store the value
	 * @param mixed  $value The value you want to cache
	 * @param integer $ttl   Optionnal TTL for the cache entry
	 */
	public function setKey($key,$value,$ttl=null){
		if(is_null($ttl)) $ttl = $this->_ttl;
		return $this->getConnector()->setKey($key,$value,$ttl);
	}

	/**
	 * Return a key from the cache if it exists
	 * @param  string $key The key you want to retrieve
	 * @return mixed the value, false if we can't get the value , null if the value doesn't exists ( meaning storing null value is not a good idea)
	 */
	public function getKey($key){
		return $this->getConnector()->getKey($key);
	}

	/**
	 * Return true if a particular key exists in the cache
	 * @param  string  $key The key you want to test
	 * @return boolean
	 */
	public function hasKey($key){
		return $this->getConnector()->hasKey($key);
	}

	/**
	 * Delete a particular key from the cache , use deleteKeys if you want to delete multiple keys
	 * @param  string $key The key you want to delete
	 * @return boolean
	 */
	public function deleteKey($key){
		return $this->getConnector()->deleteKey($key);
	}

	/**
	 * Delete keys by a regex matching key names
	 * @param  string $keyRegex A regex to match againt
	 * @return boolean
	 */
	public function deleteKeys($keyRegex){
		if(method_exists($this->getConnector(), 'deleteKeys')){
			$this->getConnector()->deleteKeys($keyRegex);
		}else{
			return null;
		}
	}

}