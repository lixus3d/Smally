<?php

namespace Smally\Dao;

class Db implements InterfaceDao {

	const STATEMENT_INSERT = 'INSERT INTO';
	const STATEMENT_UPDATE = 'UPDATE';

	protected $_table = null;
	protected $_primaryKey = null;
	protected $_voName = null;
	protected $_connector = null;

	protected $_getByIdCache = array();
	protected $_filterMethodCache = array();

	protected $_logLevel = null;
	protected $_logger = null;

	/**
	 * Define automatically the logger from the Application
	 */
	public function __construct(){
		if($logger = \Smally\Application::getInstance()->getLogger()){
			$this->_logger = $logger;
			$this->_logLevel = $logger->getLogLevel('dao');
		}
	}

	/**
	 * Set the connector the \Smally\Dao\Db will use to perform request
	 * @param mixed $connector A valid database connector
	 */
	public function setConnector($connector){
		$this->_connector = $connector;
	}

	/**
	 * Define the voName name for use in all SELECT statement
	 * @param string $class The value object class name
	 */
	public function setVoName($class){
		$this->_voName = $class;
		return $this;
	}

	/**
	 * Define the $table for every STATEMENT
	 * @param string $table the table you want to request
	 * @return \Smally\Dao\Db
	 */
	public function setTable($table){
		$this->_table = $table;
		return $this;
	}

	/**
	 * Define the primaryKey for the request of the dao
	 * @param string $primaryKey the name of the key
	 * @return \Smally\Dao\Db
	 */
	public function setPrimaryKey($primaryKey){
		$this->_primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * Get the table for request of the dao
	 * @return string
	 */
	public function getTable(){
		return $this->_table;
	}

	/**
	 * Get the primaryKey for the Dao
	 * @return string
	 */
	public function getPrimaryKey(){
		return $this->_primaryKey;
	}

	/**
	 * Get the value object class name
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	/**
	 * Return the Dao connector to perform requests
	 * @return mixed
	 */
	public function getConnector(){
		return $this->_connector;
	}

	/**
	 * Get the last inserted Id
	 * @return int
	 */
	public function getLastInsertId(){
		return $this->getConnector()->insert_id;
	}

	/**
	 * Get the number of affected rows of the last update query
	 * @return int
	 */
	public function getAffectedRows(){
		return $this->getConnector()->affected_rows;
	}

	/**
	 * Return a standard \Smally\Criteria for the current dao
	 * @return \Smally\Criteria
	 */
	public function getCriteria(){
		if($voName = $this->getVoName()){
			return \Smally\Application::getInstance()->getFactory()->getCriteria($voName);
		}
		return new \Smally\Criteria();
	}

	/**
	 * Return true if the current $vo as a utsDelete field
	 * @param  \Smally\VO\Standard  $vo A valid value object or null to instanciate an empty one
	 * @return boolean
	 */
	public function hasUtsDelete($vo=null){
		if(is_null($vo)&&$voName=$this->getVoName()){
			$vo = new $voName();
		}
		if($vo instanceof \Smally\VO\Standard){
			return property_exists($vo, 'utsDelete');
		}
		return false;
	}

	/**
	 * Wrapper to the Logger but test if we have to log before sending
	 * @param  string $text     Usually the request to log
	 * @param  int $level       The level of the log
	 * @param  int $destination Destination of the log
	 * @return null
	 */
	public function log($text,$level=\Smally\Logger::LVL_INFO,$destination=\Smally\Logger::DEST_MYSQL){
		if(!is_null($this->_logger)&&$this->_logLevel<=$level){
			$this->_logger->log($text,$level,$destination);
		}
	}

	/**
	 * Return a ValueObject by it's primary id
	 * @param  int $id               The id of the value object you want
	 * @return \stdClass
	 */
	public function getById($id,$force=false){
		if( !isset($this->_getByIdCache[$id]) || $force ){
			$primaryKey = $this->getPrimaryKey();
			$criteria = $this->getCriteria()
								->setFilter(array($primaryKey=>array('value'=>$id)))
								;
			$this->_getByIdCache[$id] = $this->fetch($criteria);
		}
		return $this->_getByIdCache[$id];
	}

	public function getByIdCache($id){
		return isset($this->_getByIdCache[$id])?$this->_getByIdCache[$id]:null;
	}

	/**
	 * Return a ValuObject if exists in data with this $values
	 * @param  array  $values Array of $property => $value to find in DB
	 * @return \Smally\VO\Standard
	 */
	public function exists(array $values){
		$criteria = $this->getCriteria();
		foreach($values as $key => $value){
			$criteria->setFilter(array($key=>array('value'=>$value)));
		}							;
		return $this->fetch($criteria);
	}

	public function query($sql){
		$this->log($sql);
		return $this->getConnector()->query($sql);
	}

	/**
	 * Return a specific element from the $criteria
	 * @param  \Smally\Criteria $criteria         The criteria to filter the data
	 * @return \stdClass
	 */
	public function fetch(\Smally\Criteria $criteria, $fetchVoName=null){

		$sql = $this->criteriaToSelect($criteria);

		$this->log($sql);

		if($result = $this->getConnector()->query($sql)){
			if($result->num_rows==1){
				if(is_null($fetchVoName)) $fetchVoName = $this->getVoName();
				$object = $this->fetchValueObject($result,$fetchVoName);
				$result->free();
				return $object;
			}elseif($result->num_rows>1) throw new \Smally\Exception('Fetch return more than one entry : '.$result->num_rows);
		}else throw new \Smally\Exception('Db fetch error : '.$this->getConnector()->error . NN . 'Query : '.$sql);

		return null;
	}

	/**
	 * Return specific elements from the $criteria
	 * @param  \Smally\Criteria $criteria         The criteria to filter the data
	 * @return array
	 */
	public function fetchAll(\Smally\Criteria $criteria=null, $fetchVoName=null){
		$return = array();

		if(is_null($criteria)) $criteria = $this->getCriteria();
		if( $this->hasUtsDelete() && !$criteria->hasFilter('utsDelete') ) $criteria->setFilter(array('utsDelete'=>array('value'=>0)));

		$sql = $this->criteriaToSelect($criteria);

		$this->log($sql);

		if($result = $this->getConnector()->query($sql)){
			if($result->num_rows>=1){
				if(is_null($fetchVoName)) $fetchVoName = $this->getVoName();
				while($object = $this->fetchValueObject($result,$fetchVoName)){
					$this->_getByIdCache[$object->getId()] = $object;
					$return[] = $object;
				}
				$result->free();
			}
		}else throw new \Smally\Exception('Db fetch error : '.$this->getConnector()->error . NN . 'Query : '.$sql);

		return $return;
	}

	/**
	 * Return the number of rows in a request with criteria
	 * @param  \Smally\Criteria $criteria The criteria to test
	 * @return int
	 */
	public function fetchCount(\Smally\Criteria $criteria=null){
		$return = 0;

		if(is_null($criteria)) $criteria = $this->getCriteria();
		if( $this->hasUtsDelete() && !$criteria->hasFilter('utsDelete') ) $criteria->setFilter(array('utsDelete'=>array('value'=>0)));

		$params = $this->criteriaToSql($criteria);
		$params['fields'] = array('COUNT(*) as nb');
		extract($params);
		$sql = $this->makeSelect($where,$order,$limit,$fields,$join,$groupby);

		$this->log($sql);

		if($result = $this->getConnector()->query($sql)){
			if($result->num_rows>=1){
				$return = $result->fetch_object()->nb;
				$result->free();
			}
		}else throw new \Smally\Exception('Db fetch error : '.$this->getConnector()->error . NN . 'Query : '.$sql);

		return $return;
	}

	/**
	 * Store a value object. Use INSERT or UPDATE in case of $primaryKey not null
	 * @param  \stdClass $vo The value object you want to store
	 * @param  const $statement Force a particular statement (use Dao constant)
	 * @return boolean true if store succeded
	 */
	public function store($vo,$statement=null){

		// pseudo event system
		if(method_exists($vo, 'onStore')){
			$vo->onStore($statement);
		}

		// get the primary key
		$primaryKey = $this->getPrimaryKey();

		// determine if we insert or update the data
		if(property_exists($vo,$primaryKey)&&$vo->{$primaryKey}!=''){
			$statement = is_null($statement)?self::STATEMENT_UPDATE:$statement;
			if(property_exists($vo,'utsUpdate')) $vo->utsUpdate = time();
		}else{
			$statement = is_null($statement)?self::STATEMENT_INSERT:$statement;
			if(property_exists($vo,'utsCreate')) $vo->utsCreate = time();
		}

		// define each field
		$set = array();
		foreach($vo as $property => $value){
			if($property == $primaryKey) continue;
			$set[] = '`'.$property.'` = \''.$this->getConnector()->escape_string($value).'\'';
		}

		$sql = $statement.' `'.$this->getTable().'` SET '.implode(',',$set);

		if($statement == self::STATEMENT_UPDATE) $sql.= ' WHERE `'.$primaryKey.'` = \''.$vo->{$primaryKey}.'\'';

		$this->log($sql);

		if($return = $this->getConnector()->query($sql)){
			if($statement==self::STATEMENT_INSERT&&!$vo->{$primaryKey}){
				$vo->{$primaryKey} = $this->getLastInsertId();
			}
		}

		// pseudo event system
		if($return){
			if(method_exists($vo, 'onStoreSuccess')){
				$vo->onStoreSuccess($statement);
			}
		}else{
			$this->log('Error in : '.$sql,\Smally\Logger::LVL_ERROR);
			$this->log($this->getConnector()->errno.' : '.$this->getConnector()->error,\Smally\Logger::LVL_ERROR);
			if(method_exists($vo, 'onStoreFail')){
				$vo->onStoreFail($statement);
			}
		}

		return $return;
	}

	/**
	 * Delete a value object. Use utsDelete if the valueObject contains the property
	 * @param  int $id               The id of the value object you want to delete
	 * @param  boolean $forceDelete  Force the use of DELETE FROM
	 * @return boolean true if delete succeded
	 */
	public function delete($vo,$forceDelete=false){
		$primaryKey = $this->getPrimaryKey();

		if($this->hasUtsDelete($vo)&&!$forceDelete){
			$sql = 'UPDATE `'.$this->getTable().'` SET utsDelete=UNIX_TIMESTAMP() WHERE `'.$primaryKey.'` = \''.$this->getConnector()->escape_string($vo->getId()).'\'';
		}else{
			$sql = 'DELETE FROM `'.$this->getTable().'` WHERE `'.$primaryKey.'` = \''.$this->getConnector()->escape_string($vo->getId()).'\'';
		}

		$this->log($sql);

		return $this->getConnector()->query($sql);
	}


	/**
	 * Fetch a $result to the given $valuObjectClass
	 * @param  \mysqli_result $result           mysqli_result object to fetch
	 * @param  string $voName Optionnal voName that will be return, stdClass if not given
	 * @return \stdClass
	 */
	public function fetchValueObject($result,$voName=null){
		if($voName){
			return $result->fetch_object($voName);
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
		extract($this->criteriaToSql($criteria));
		return $this->makeSelect($where,$order,$limit,$fields,$join,$groupby);
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
	public function makeSelect($where=array(),$order=array(),$limit=null,$fields='*',$join=null,$groupby=null){

		if(!is_array($fields)||count($fields)==0) $fields = array($fields?:'*');
		foreach($fields as &$field){
			if(strpos($field, '*')!==false){
				if($field === '*') $field = '`'.$this->getTable().'`.*';
				continue;
			}elseif(strpos($field, '.')===false){
				$field = '`'.$this->getTable().'`.`'.$field.'`';			
				continue;
			}elseif(preg_match('#^(COUNT|SUM)#',$field)){
				continue;
			}
			$field = '`'.$field.'`';
		}

		$sql  = 'SELECT '.implode(', ',$fields);
		$sql .= ' FROM `'.$this->getTable().'`';
		if($join)
			$sql .= ' '.implode(' ',$join);
		if($where)
			$sql .= ' WHERE '.implode(' AND ',$where);
		if($groupby)
			$sql .= ' GROUP BY '.implode(', ',$groupby);
		if($order)
			$sql .= ' ORDER BY '.implode(', ',$order);
		if($limit)
			$sql .= ' LIMIT '.$limit[0].','.$limit[1];

		return $sql;
	}

	/**
	 * Convert a criteria object to a bunch of sql request parameters
	 * @param  \Smally\Criteria $criteria The criteria to convert
	 * @return array The return is compose of array($table,$where,$order,$limit,$fields,$join,$groupby)
	 */
	public function criteriaToSql(\Smally\Criteria $criteria){

		$fields = array();
		$join = array();
		$where = array();
		$order = array();
		$limit = array();
		$values = array();
		$groupby = array();

		$fields = $criteria->getFields();

		$filter = $criteria->getFilter();
		foreach($filter as $field => $params){

			$value = isset($params['value'])?$params['value']:'0';
			$operator = isset($params['operator'])?$params['operator']:(is_array($value)?'IN':'=');


			// field or operator 'search' means to do a search in field(s) with LIKE
			if( $field === 'search' || $operator === 'search' ) {
				if($field === 'search'){
					if($voName = $this->getVoName()){
						$vo = new $this->_voName();
						$searchFields = $vo->getSearchFields();
					}else throw new \Smally\Exception('field \'search\' in the criteria but no Vo define in the dao !');
				}else{
					if(isset($params['fields'])&&$params['fields']) $searchFields = $params['fields'];
					else throw new \Smally\Exception('operator \'search\' in the criteria but no \'fields\' key in the params !');
				}
				if($searchFields && $likeTest = $this->toLike($value, $searchFields,$params) ){
					$where[] = $likeTest;
				}
				continue;
			}


			if(!is_array($params)) $where[] = $params;
			else{

				// Special Filter must be defined in a dao extends
				if( (isset($this->_filterMethodCache['filter'.$field]) && $this->_filterMethodCache['filter'.$field]) || (!isset($this->_filterMethodCache['filter'.$field]) && method_exists($this, 'filter'.$field)) ){
					$this->_filterMethodCache['filter'.$field] = true;
					$this->{'filter'.$field}($value,$operator,$params,$filter,$where,$join,$continue);
					if(!$continue) continue;
				}else{
					$this->_filterMethodCache['filter'.$field] = false;
				}

				switch($operator){
					case '=':
					case '>':
					case '<':
					case '>=':
					case '<=':
					case '!=':
					case 'LIKE':
					case 'NOT LIKE':
						$where[$field] = '`'.$this->getTable().'`.`'.$field.'` '.$operator.' \''.$this->getConnector()->real_escape_string($value).'\'';
						break;
					case 'IN':
					case 'NOT IN':
						foreach($value as &$val){
							$val = '\''.$this->getConnector()->real_escape_string($val).'\'';
						}
						$where[$field] = '`'.$this->getTable().'`.`'.$field.'` '.$operator.' ('.implode(',',$value).')';
						break;
				}
			}
		}

		foreach($criteria->getOrder() as $orderField){
			$order[] = '`'.$this->getTable().'`.`'.$orderField[0].'` '.$orderField[1];
		}

		if(is_array($criteria->getLimit())){
			$limit = $criteria->getLimit();
		}

		return array('where'=>$where,'order'=>$order,'limit'=>$limit,'fields'=>$fields,'join'=>$join,'groupby'=>$groupby);
	}

	/**
	 * Return a LIKE part for a sql request from the $value search into $fields
	 * @param  string $value  The search string
	 * @param  array $fields The fields to search in
	 * @return string
	 */
	public function toLike($value,$fields,$options=array()){

		$finalTest = '1=0';

		$patterns = array();

		$value = (string) trim($value,' ,');

		// extract all exact sentence > "foo bar" and 'foo bar'
		if(preg_match_all('#(["\'])((?!\\1).+)\\1#iU',$value,$matches,PREG_SET_ORDER)){
			foreach($matches as $match){
				$patterns[]= $match[2];
				$value = str_replace($match[0],'',$value);
			}
		}

		// extract all others words
		$value = str_replace(array('`'),array('\''),$value);
		$value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);
		$value = preg_replace('#[^a-zA-Z0-9 \'",.-]#','',$value);
		$words = explode(' ',str_replace(',',' ',$value));
		foreach($words as $word){
			if(!$word) continue;
			$patterns[] = $word;
		}

		$parts = array();
		$crossParts = array();

		if($patterns){
			foreach($fields as $fieldName){
				$like = array();
				foreach($patterns as $pattern){
					$test = $fieldName.' LIKE \'%'. $this->getConnector()->real_escape_string($pattern) .'%\'';
					$like[] = $test;
				}
				$parts[] 		= '(' . implode(' AND ',$like) . ')' ;
				$crossParts[] 	= '(' . implode(' OR ', $like) . ')' ;
			}
		}

		if($parts && $crossParts){
			$finalTest = '( ('.implode(' OR ',$parts).') OR ('.implode(' AND ',$crossParts).') )';
		}

		return $finalTest;

	}

}