<?php

namespace Smally;

class Meta{

	protected $_application = null;

	protected $_default = array(
				'title' 		=> array(),
				'keywords' 		=> array(),
				'description' 	=> array(),
			);

	protected $_metas = array(
				'title' 		=> array(),
				'keywords' 		=> array(),
				'description' 	=> array(),
			);

	protected $_otherMetas = array();

	/**
	 * Construct the global $context object
	 * @author Lixus3d <developpement@adreamaline.com>
	 * @param array $vars
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	public function getApplication(){
		return $this->_application;
	}

	public function addMeta($type,$content='',$default=false){
		$dest = $default? '_default' : '_metas';
		$this->{$dest}[$type][] = $content;
		return $this;
	}

	/**
	 * Add specific meta tag, likes robots tag
	 * @param array $tag
	 */
	public function addMetaTag($tag){
		if(is_array($tag)){
			$this->_otherMetas[] = $tag;
			return $this;
		}
	}

	/**
	 * Get a particular meta
	 * @param string $type title, keywords or description
	 * @return mixed
	 */
	public function getType($type){
		if(isset($this->_metas[$type]) && $this->_metas[$type]) $m = $this->_metas;
		elseif(isset($this->_default[$type]) && $this->_default[$type]) $m = $this->_default;

		if(isset($m)){
			return implode(' - ',$m[$type]);
		}
		return null;
	}

	/**
	 * Return the array of other metas (robots, etc...)
	 */
	public function getOtherMetas(){
		return $this->_otherMetas;
	}




}