<?php

namespace Smally;

class Criteria {

	protected $_where = array();
	protected $_order = array();
	protected $_limit = null;

	/**
	 * Construct a new criteria with the given options
	 * @param array $filter Filter array
	 * @param mixed $order  Order array or string
	 * @param mixed $limit  Limit array or int
	 */
	public function __construct($filter=null,$order=null,$limit=null){
		if(!is_null($filter)){
			$this->setFilter($filter);
		}
		if(!is_null($order)){
			$this->setOrder($order);
		}
		if(!is_null($limit)){
			$this->setLimit($limit);
		}
	}

	/**
	 * Return a string representation of the current criteria
	 * @return string
	 */
	public function __toString(){
		$data = '';
		$data .= serialize($this->_where);
		$data .= serialize($this->_order);
		$data .= serialize($this->_limit);
		return $data;
	}

	/**
	 * Mix two criteria together
	 * @param \Smally\Criteria $criteria
	 */
	public function import(\Smally\Criteria $criteria){
		$this->setFilter($criteria->getFilter());
		$this->setOrder($criteria->getOrder());
		$this->setLimit($criteria->getLimit());
	}

	/**
	 * Return the where conditions of the criteria
	 * @return array
	 */
	public function getFilter(){
		return $this->_where;
	}

	/**
	 * Return the order part of the criteria
	 * @return array
	 */
	public function getOrder(){
		return $this->_order;
	}

	/**
	 * Return the limit of the criteria can be either null or array
	 * @return mixed
	 */
	public function getLimit(){
		return $this->_limit;
	}

	/**
	 * Set filter(s) of the criteria
	 * @example $filter = array('mykey'=>array('value'=>10,'operator'=>'<=')) , $filter = array('mykey2'=>array('value'=>array(5,16),'operator'=>'IN'))
	 * @param array $filter Associative array of $field => $filter ;
	 * @return \Smally\Criteria
	 */
	public function setFilter($filter){
		if(is_array($filter) && $filter){
			foreach($filter as $field => $params){
				$this->_where[$field] = $params;
			}
		}
		return $this;
	}

	/**
	 * Set the order of the criteria
	 * @param string $order The order to add
	 * @param boolean $replace Does this order must replace any existing order
	 * @return \Smally\Criteria
	 */
	public function setOrder($order,$replace=false){
		if(is_array($order)){
			foreach($order as $key => $field){
				if(is_array($field)){
					if($replace) $this->_order = array($field);
					else $this->_order[] = $field;
				}
			}
		}elseif($order){
			$order = explode(' ',$order);
			if(isset($order[0])){
				$field = $order[0];
				if(isset($order[1]) && preg_match('#asc|desc#i',$order[1])) $direction = $order[1];
				else $direction = 'ASC';
				$this->setOrder(array(array($field,$direction)),$replace);
			}
		}
		return $this;
	}

	/**
	 * Set the limit of the criteria
	 * @param mixed $value Limit you want , either int or array of offset / value ;
	 */
	public function setLimit($value){
		if(is_array($value)){
			if(count($value) == 2 ){
				$this->_limit =  $value;
			}
		}else{
			if($value > 0) $this->_limit = array(0,$value);
		}
		return $this;
	}

	/**
	 * Set every associative array to an equal filter
	 * @example You can use the 'search' key or operator that will search on multiple field using like
	 * @param  array $array An array or class with associative key => value to use has filter
	 * @return \Smally\Criteria
	 */

	public function arrayToFilter($array,$vo=null){
		$filter = array();

		foreach($array as $key => $value){
			$value = (string) $value;

			if($value === '') continue; // we don't filter on empty value , carefull 0 is a correct value
			if($key != 'search' && !is_null($vo) && !property_exists($vo,$key)) continue;

			switch($key){
				case strpos($key,'uts')===0:
					list($day,$month,$year) = explode('/',$value);
					if(!is_numeric($day)||!is_numeric($month)||!is_numeric($year)) continue(2);
					$value = mktime(0,0,0,$month,$day,$year);
				break;
			}

			$filter[$key] = array('value'=>$value);
		}
		$this->setFilter($filter);
		return $this;
	}
}