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
	protected $_headTags = array();

	/**
	 * Construct the meta object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Meta
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
	 * Add a meta
	 * @param string  $type    title, keywords or description
	 * @param string  $content Any content for the meta
	 * @param boolean $default Is this a default meta ?
	 * @return \Smally\Meta
	 */
	public function addMeta($type,$content='',$default=false){
		if($content){
			$dest = $default? '_default' : '_metas';
			$this->{$dest}[$type][] = $content;
		}
		return $this;
	}

	/**
	 * Add specific meta tag, likes robots tag
	 * @param array $tag Attributes of the tag in key => value format
	 * @return \Smally\Meta
	 */
	public function addMetaTag($tag){
		if(is_array($tag)){
			if(isset($tag['name'])&&$tag['name']){
				$this->_otherMetas[$tag['name']] = $tag;
			}else{
				$this->_otherMetas[] = $tag;
			}
		}
		return $this;
	}

	/**
	 * Add a head tag ( rel prev, next, etc ...)
	 * @param string $tagType  tag name (link for example)
	 * @param array  $tagAttr  tag attributes
	 * @param string $uniqName A uniqname to avoid multiple entries
	 */
	public function addHeadTag($tagType,$tagAttr=array(),$uniqName=null){
		if($tagType){
			$tagArray = array('tag'=>$tagType,'attr'=>$tagAttr);
			if(is_null($uniqName)){
				$this->_headTags[] = $tagArray;
			}else{
				$this->_headTags[$uniqName] = $tagArray;
			}
		}
		return $this;
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
			switch($type){
				case 'keywords':
					$sep = ',';
					break;
				default :
					$sep = ' - ';
					break;
			}
			return implode($sep,$m[$type]);
		}
		return null;
	}

	/**
	 * Return the array of other metas (robots, etc...)
	 * @return array
	 */
	public function getOtherMetas(){
		return $this->_otherMetas;
	}

	/**
	 * Return the array of head tags (link, etc...)
	 * @return array
	 */
	public function getHeadTags(){
		return $this->_headTags;
	}




}