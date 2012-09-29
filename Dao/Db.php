<?php

namespace Smally\Dao;

class Db implements InterfaceDao {

	protected $_table = '';
	protected $_valueObjectClass = null;
	protected $_connector = null;

	/**
	 * Set the connector the \Smally\Dao\Db will use to perform request
	 * @param mixed $connector A valid database connector
	 */
	public function setConnector($connector){
		$this->_connector = $connector;
	}

	/**
	 * Define the valueObjectClass name for use in all SELECT statement
	 * @param string $class The value object class name
	 */
	public function setValueObjectClass($class){
		$this->_valueObjectClass = $class;
		return $this;
	}

	/**
	 * Define the $table for every STATEMENT
	 * @param string $table the table you want to request
	 */
	public function setTable($table){
		$this->_table = $table;
		return $this;
	}

	/**
	 * Return the Dao connector to perform requests
	 * @return mixed
	 */
	public function getConnector(){
		return $this->_connector;
	}

	/**
	 * Return a standard \Smally\Criteria for the current dao
	 * @return \Smally\Criteria
	 */
	public function getCriteria(){
		$criteria = new \Smally\Criteria();
		$criteria->setTable($this->_table);
		return $criteria;
	}

	/**
	 * Return a ValueObject by it's primary id
	 * @param  int $id               The id of the value object you want
	 * @param  string $valueObjectClass Optionnal : ValueObjectClass that will be return, stdClass if not given
	 * @param  string $primaryKey       Optionnal : The name of the primary key , if not given {$table.'Id'} or 'id' use instead
	 * @return \stdClass
	 */
	public function getById($id,$valueObjectClass=null,$primaryKey=null){
		if(is_null($primaryKey)) $primaryKey = $this->getPrimaryKey();
		$criteria = $this->getCriteria()
							->setFilter(array($primaryKey=>array('value'=>$id)))
							;
		return $this->fetch($criteria,$valueObjectClass);
	}

	/**
	 * Return a specific element from the $criteria
	 * @param  \Smally\Criteria $criteria         The criteria to filter the data
	 * @param  string           $valueObjectClass Optionnal ValueObjectClass that will be return, stdClass if not given
	 * @return \stdClass
	 */
	public function fetch(\Smally\Criteria $criteria,$valueObjectClass=null){

		$sql = $this->criteriaToSelect($criteria);

		if($result = $this->getConnector()->query($sql)){
			if($result->num_rows==1){
				$object = $this->fetchValueObject($result,$valueObjectClass?:$this->_valueObjectClass);
				$result->free();
				return $object;
			}elseif($result->num_rows>1) throw new \Smally\Exception('Fetch return more than one entry : '.$result->num_rows);
		}else throw new \Smally\Exception('Db fetch error : '.$this->getConnector()->error . NN . 'Query : '.$sql);

		return null;
	}

	/**
	 * Return specific elements from the $criteria
	 * @param  \Smally\Criteria $criteria         The criteria to filter the data
	 * @param  string           $valueObjectClass Optionnal ValueObjectClass that will be return, stdClass if not given
	 * @return array
	 */
	public function fetchAll(\Smally\Criteria $criteria,$valueObjectClass=null){
		$return = array();

		$sql = $this->criteriaToSelect($criteria);

		if($result = $this->getConnector()->query($sql)){
			if($result->num_rows>=1){
				while($object = $this->fetchValueObject($result,$valueObjectClass?:$this->_valueObjectClass)){
					$return[] = $object;
				}
				$result->free();
			}
		}else throw new \Smally\Exception('Db fetch error : '.$this->getConnector()->error . NN . 'Query : '.$sql);

		return $return;
	}

	/**
	 * Store a value object. Use INSERT or UPDATE in case of $primaryKey not null
	 * @param  \stdClass $vo The value object you want to store
	 * @return boolean true if store succeded
	 */
	public function store($vo,$primaryKey=null){

		// get the primary key
		if(is_null($primaryKey)) $primaryKey = $this->getPrimaryKey();

		// determine if we insert or update the data
		if(!is_null($vo->{$primaryKey})&&$vo->{$primaryKey}!=''){
			$statement = 'UPDATE';
			if(isset($vo->utsCreate)) $vo->utsCreate = time();
		}else{
			$statement = 'INSERT INTO';
			if(isset($vo->utsUpdate)) $vo->utsUpdate = time();
		}

		// define each field
		$set = array();
		foreach($vo as $property => $value){
			if($property == $primaryKey) continue;
			$set[] = '`'.$property.'` = \''.$this->getConnector()->escape_string($value).'\'';
		}

		$sql = $statement.' '.$this->_table.' SET '.implode(',',$set);

		if($statement == 'UPDATE') $sql.= ' WHERE `'.$primaryKey.'` = \''.$vo->{$primaryKey}.'\'';

		return $this->getConnector()->query($sql);
	}

	/**
	 * Delete a value object. Use utsDelete if the valueObject contains the property
	 * @param  int $id               The id of the value object you want to delete
	 * @return boolean true if delete succeded
	 */
	public function delete($id,$utsDeleteMode=true){
		if(is_null($primaryKey)) $primaryKey = $this->getPrimaryKey();

		if($utsDeleteMode){
			$sql = 'UPDATE '.$this->_table.' SET utsDelete=UNIX_TIMESTAMP() WHERE `'.$primaryKey.'` = \''.$id.'\'';
		}else{
			$sql = 'DELETE FROM '.$this->_table.' WHERE `'.$primaryKey.'` = \''.$id.'\'';
		}
		return $this->getConnector()->query($sql);
	}

	/**
	 * Get the last inserted Id
	 * @return int
	 */
	public function lastInsertId(){
		return $this->getConnector()->insert_id;
	}

	/**
	 * Get the number of affected rows of the last update query
	 * @return int
	 */
	public function affectedRows(){
		return $this->getConnector()->affected_rows;
	}

	/**
	 * Get the generic primaryKey for the Dao
	 * @return string
	 */
	public function getPrimaryKey(){
		return $this->_table ? $this->_table.'Id' : 'id';
	}

	/**
	 * Fetch a $result to the given $valuObjectClass
	 * @param  \mysqli_result $result           mysqli_result object to fetch
	 * @param  string $valueObjectClass Optionnal ValueObjectClass that will be return, stdClass if not given
	 * @return \stdClass
	 */
	public function fetchValueObject($result,$valueObjectClass=null){
		if($valueObjectClass){
			return $result->fetch_object($valueObjectClass);
		}else{
			return $result->fetch_object();
		}
	}

	/**
	 * Convert a \Smally\Criteria $criteria to a mysql SELECT statement
	 * @param  \Smally\Criteria $criteria The criteria to convert
	 * @return string
	 */
	public function criteriaToSelect(\Smally\Criteria $criteria){
		return $this->makeSelect($this->criteriaToSql($criteria));
	}

	/**
	 * Create a SELECT statement from the parameters
	 * @param  mixed $table   	The table name or an array of all parameters
	 * @param  array  $where   	Where clauses
	 * @param  array  $order   	Order of the request
	 * @param  array $limit   	the limit of the request
	 * @param  string $fields 	fields to select
	 * @param  array $join    	join parts
	 * @param  array $groupby 	groupby part
	 * @return string
	 */
	public function makeSelect($table,$where=array(),$order=array(),$limit=null,$fields='*',$join=null,$groupby=null){
		if(is_array($table)) list($table,$where,$order,$limit,$fields,$join,$groupby) = $table; // instead of enumerate each parameter you can give an associative array


		if( is_null($table) && ($table=='') && !($table = $this->_table) ) throw new \Smally\Exception('Invalid parameter for makeSelect : table is missing');

		if(!is_array($fields)||count($fields)==0) $fields = array($fields?:'*');
		foreach($fields as &$field){
			if($field === '*') continue;
			$field = '`'.$field.'`';
		}

		$sql  = 'SELECT '.implode(', ',$fields);
		$sql .= ' FROM '.$table;
		if($join)
			$sql .= ' '.implode(' ',$join);
		if($where)
			$sql .= ' WHERE '.implode(' AND ',$where);
		if($groupby)
			$sql .= ' GROUP BY '.implode(', ',$groupby);
		if($order)
			$sql .= ' ORDER BY '.implode(', ',$order);
		if($limit)
			$sql .= ' LIMIT '.implode(',',$limit);

		return $sql;
	}

	/**
	 * Convert a criteria object to a bunch of sql request parameters
	 * @param  \Smally\Criteria $criteria The criteria to convert
	 * @return array The return is compose of array($table,$where,$order,$limit,$fields,$join,$groupby)
	 */
	public function criteriaToSql(\Smally\Criteria $criteria){

		$table = $criteria->getTable();
		$fields = array();
		$join = array();
		$where = array();
		$order = array();
		$limit = array();
		$values = array();
		$groupby = array();

		foreach($criteria->getFilter() as $field => $params){
			if(!is_array($params)) $where[] = $params;
			else{
				$operator = isset($params['operator'])?$params['operator']:'=';
				$value = isset($params['value'])?$params['value']:'0';
				switch($operator){
					case '=':
					case '>':
					case '<':
					case '>=':
					case '<=':
					case '!=':
						$where[$field] = '`'.$field.'` '.$operator.' \''.$this->getConnector()->real_escape_string($value).'\'';
						break;
					case 'IN':
					case 'NOT IN':
						foreach($value as &$val){
							$val = '\''.$this->getConnector()->real_escape_string($val).'\'';
						}
						$where[$field] = '`'.$field.'` '.$operator.' '.implode(',',$value);
						break;
				}
			}
		}

		foreach($criteria->getOrder() as $orderField){
			$order[] = '`'.$orderField[0].'` '.$orderField[1];
		}

		if(is_array($criteria->getLimit())){
			$limit = $criteria->getLimit();
		}

		return array($table,$where,$order,$limit,$fields,$join,$groupby);
	}
}