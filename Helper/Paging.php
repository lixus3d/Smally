<?php

namespace Smally\Helper;

class Paging {

	protected $_decoratorNamespace = '\\Smally\\Helper\\Decorator';

	protected $_urlParam = 'p';

	protected $_nbPages = 0;
	protected $_nbItems = 0;
	protected $_limit = 10;
	protected $_page = 0; // 0 is the first page, the setPage correct automatically by substracting 1 to the given value

	protected $_url = null;

	protected $_attributes  = array();
	protected $_attributesElement = array();

	public function __construct($limit=null,$urlParam=null){
		$this->setLimit($limit);
		$this->setUrlParam($urlParam);
	}

	/**
	 * Define the url param for get and set the page in the url
	 * @param string $key The name of the param
	 * @return  \Smally\Helper\Paging
	 */
	public function setUrlParam($key){
		$this->_urlParam = $key ?: ((string)\Smally\Application::getInstance()->getConfig()->smally->default->paging->urlParam?:'p');
		return $this;
	}

	/**
	 * Define the limit per page of items
	 * @param int $limit Number of items you want to see per page
	 * @return \Smally\Helper\Paging
	 */
	public function setLimit($limit){
		$this->_limit = $limit>0 ? $limit : ((string)\Smally\Application::getInstance()->getConfig()->smally->default->paging->limit?:10);
		return $this;
	}

	/**
	 * Define the number of pages in this paging
	 * @param int $nbPages [description]
	 * @return \Smally\Helper\Paging
	 */
	public function setNbPages($nbPages){
		$this->_nbPages = $nbPages;
		return $this;
	}

	/**
	 * Define the total number of items that can be shown
	 * @param int $nbItems Total number of items that can be shown
	 * @return \Smally\Helper\Paging
	 */
	public function setNbItems($nbItems){
		$this->_nbItems = $nbItems;
		$this->setNbPages( ceil($this->getNbItems() / $this->getLimit()) );
		return $this;
	}

	/**
	 * Define the actual page number, stored substracted of 1
	 * @example Giving 1 will store 0 ; Giving 0 will store 0 ; Giving 2 will store 1
	 * @param int $page The number of the current page
	 * @return \Smally\Helper\Paging
	 */
	public function setPage($page=null){
		// if $page is null we try to get it from context
		if(is_null($page)){
			$page = (string) \Smally\Application::getInstance()->getContext()->{$this->_urlParam};
		}
		$page = $page > ceil($this->getNbItems() / $this->getLimit()) ? ceil($this->getNbItems() / $this->getLimit()) : $page;
		$page = ($page-1>0) ? $page-1 : 0;
		$this->_page = $page;
		return $this;
	}

	/**
	 * Define the decorator namespace to use for the menu
	 * @param string $ns namespace
	 * @return  \Smally\Helper\Paging
	 */
	public function setDecoratorNamespace($ns){
		$this->_decoratorNamespace = $ns;
		return $this;
	}

	/**
	 * Define an attribute for the paging tag
	 * @param string $attribute the attribute name to define
	 * @param mixed $value the value
	 * @return \Smally\Helper\Paging
	 */
	public function setAttribute($attribute,$value,$type='_attributes'){
		switch($attribute){
			case 'class':
				if(!isset($this->{$type}[$attribute])) $this->{$type}[$attribute] = array();
				$this->{$type}[$attribute][] = $value;
			break;
			default:
				$this->{$type}[$attribute] = $value;
			break;
		}
		return $this;
	}

	/**
	 * Set an attribute for the pagenumber elements
	 * @param string $attribute The attribute name
	 * @param string $value     The value of the attribute
	 * @return \Smally\Helper\Paging
	 */
	public function setAttributeElement($attribute,$value){
		return $this->setAttribute($attribute,$value,'_attributesElement');
	}

	/**
	 * Set the base url to use for each page number element
	 * @param string $url The base url
	 * @return \Smally\Helper\Paging
	 */
	public function setUrl($url){
		$this->_url = $url;
		return $this;
	}

	/**
	 * Return the limit per page of the current paging
	 * @return int
	 */
	public function getLimit(){
		return $this->_limit;
	}

	/**
	 * Return the total number of pages in this paging
	 * @return int
	 */
	public function getNbPages(){
		return $this->_nbPages;
	}

	/**
	 * Return the total number of items in the context of this paging
	 * @return int
	 */
	public function getNbItems(){
		return $this->_nbItems;
	}

	/**
	 * Return the current page fixed for the paging
	 * @return int
	 */
	public function getPage(){
		return $this->_page;
	}

	/**
	 * Return the current offset of the first item of the current page
	 * @return int
	 */
	public function getOffset(){
		return $this->getPage()*$this->getLimit();
	}

	/**
	 * Return the current interval for a request
	 * @return array array( Offset, Limit )
	 */
	public function getInterval(){
		return array($this->getOffset(),$this->getLimit());
	}

	/**
	 * Return the paging tag attributes
	 * @return array the attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}

	/**
	 * Return the paging page number element tag attributes
	 * @return array the attributes
	 */
	public function getAttributesElement(){
		return $this->_attributesElement;
	}

	/**
	 * Return the base url to use for each page number element
	 * @return string
	 */
	public function getUrl($pageNumber=1){
		return strpos($this->_url,'?')!==false ? $this->_url.'&amp;'.$this->_urlParam.'='.$pageNumber : $this->_url.'?'.$this->_urlParam.'='.$pageNumber ;
	}

	/**
	 * Get a decorator for paging helper
	 * @param  string $type Type/Name of the decorator, usually paging and pagingElement
	 * @param  mixed $obj  the object to give to the decorator
	 * @return \Smally\Helper\Decorator\AbstractDecorator
	 */
	public function getDecorator($type,$obj=null){
		$name = $this->_decoratorNamespace.ucfirst($type); // Try the current namespace
		if(!class_exists($name)){
			$name = '\\Smally\\Helper\\Decorator\\'.ucfirst($type); // try the helper default namespace
		}
		return new $name($obj);
	}

	/**
	 * Render the menu thru the menu decorator
	 * @return string
	 */
	public function render(){
		return $this->getDecorator('paging',$this)->render();
	}
}