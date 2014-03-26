<?php

namespace Smally\Helper;

class Order {

	protected $_urlParam = 'o';

	protected $_url = null;
	protected $_urlInfos = null;

	protected $_order = array();

	protected $_listing = null;

	public function __construct($urlParam=null){
		$this->setUrlParam($urlParam);
	}

	/**
	 * Define a back reference to the listing
	 * @param \Smally\Listing $listing The listing where the order is used
	 * @return  \Smally\Helper\Order
	 */
	public function setListing(\Smally\Listing $listing){
		$this->_listing = $listing;
		return $this;
	}

	/**
	 * Define the url param for get and set the order in the url
	 * @param string $key The name of the param
	 * @return  \Smally\Helper\Order
	 */
	public function setUrlParam($key){
		$this->_urlParam = $key ?: ((string)\Smally\Application::getInstance()->getConfig()->smally->default->order->urlParam?:'o');
		return $this;
	}

	/**
	 * Set the base url to use for each page number element
	 * @param string $url The base url
	 * @return \Smally\Helper\Order
	 */
	public function setUrl($url){
		$this->_url = $url;
		return $this;
	}

	/**
	 * Set the unique order field and direction
	 * @param string $field     The field you want the request to be order by
	 * @param string $direction the direction of the order
	 * @return  \Smally\Helper\Order
	 */
	public function setOrder($field=null,$direction='ASC'){

		if(is_null($field)){
			$getVar = \Smally\Application::getInstance()->getContext()->{$this->_urlParam};
			if(is_array($getVar)||($getVar instanceof \Smally\ContextStdClass)){
				$add = array(); // tricks to avoid reseting default order if no order present in the url
				foreach($getVar as $order){
					if(strpos($order,',')>=1){
						list($field,$direction) = explode(',',$order);
						if(!in_array($direction, array('ASC','DESC'))) $direction='ASC';
						$add[$field] = $direction;
					}
				}
				if($add){
					$this->_order = array();
					foreach($add as $field => $direction){
						$this->addOrder($field,$direction);
					}
				}
				return $this;
			}
		}

		if(is_string($field)&&$direction){
			$this->_order = array(array($field,$direction));
		}elseif(is_array($field)){
			$order = $field;
			$this->_order = array();
			foreach($order as $key => $oField){
				if(is_array($oField)){
					$this->_order[] = $oField;
				}
			}
		}

		return $this;
	}

	/**
	 * Add and order to the order by of the request
	 * @param string $field     The field you want to add to the request order by
	 * @param string $direction the direction of the order
	 * @return  \Smally\Helper\Order
	 */
	public function addOrder($field,$direction='ASC'){
		$this->_order[] = array($field,$direction);
		return $this;
	}

	/**
	 * Return the back reference to a listing if set before
	 * @return \Smally\Listing
	 */
	public function getListing(){
		return $this->_listing;
	}

	/**
	 * Return the url parameter for order
	 * @return string
	 */
	public function getUrlParam(){
		return $this->_urlParam;
	}

	/**
	 * Return the order array to use typically in a Criteria
	 * @return array
	 */
	public function getOrder(){
		return $this->_order;
	}

	/**
	 * Return whether a field is in actual order or not , return it's direction , null if not present
	 * @param  string $field The field to search for in the actual order
	 * @return mixed ASC or DESC if present, null otherwise
	 */
	public function inOrder($field){
		foreach($this->_order as $order){
			if($order[0]==$field) return $order[1];
		}
		return null;
	}

	/**
	 * Return the base url to use for each page number element
	 * @param  string $field The field you want the url
	 * @return string
	 */
	public function getUrl($field,$forceDirection=null){

		if(is_null($this->_urlInfos)){
			$this->_urlInfos = parse_url($this->_url);

			if(isset($this->_urlInfos['query'])) parse_str($this->_urlInfos['query'],$this->_urlInfos['params']);
			else $this->_urlInfos['params'] = array();

			if($listing = $this->getListing()){
				if($paging = $listing->getPaging()){
					$this->_urlInfos['params'][$paging->getUrlParam()] = null;
				}
			}
		}
		$params = $this->_urlInfos['params'];

		if($forceDirection){
			if($forceDirection!=='DESC') $direction = 'ASC';
			else $direction = 'DESC';
		}else{
			if(!($direction = $this->inOrder($field))) $direction='ASC';
			elseif($direction=='ASC') $direction='DESC';
			else $direction = 'ASC';
		}

		$params[$this->_urlParam] = array(implode(',',array($field,$direction)));

		return 'http://'.$this->_urlInfos['host'].$this->_urlInfos['path'].'?'.http_build_query($params);
	}

	/**
	 * Return the classes to use for a field
	 * @param  string $field The field you want the classes
	 * @return string
	 */
	public function getClass($field){
		$classes = array();

		if($direction = $this->inOrder($field)) {
			$classes[] = 'orderActive';
		}

		if($direction){
			$classes[] = 'order'.ucfirst(strtolower($direction));
		}else{
			$classes[] = 'orderInactive';
		}

		return implode(' ',$classes);
	}

	/**
	 * Return the classes to use for a field
	 * @param  string $field The field you want the classes
	 * @return string
	 */
	public function getIconClass($field){
		$classes = array();

		if($direction = $this->inOrder($field) ){
			switch(strtoupper($direction)){
				case 'ASC':
					$classes[] = 'fa-sort-asc';
					break;
				case 'DESC':
					$classes[] = 'fa-sort-desc';
					break;
			}
		}else{
			$classes[] = 'fa-sort';
		}

		return implode(' ',$classes);
	}

}
