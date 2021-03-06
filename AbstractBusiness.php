<?php

namespace Smally;

class AbstractBusiness {

	protected $_application = null;

	/**
	 * Must be define with vo name relative to business for optimum functionnality
	 * @var string
	 */
	protected $_voName = null;

	/**
	 * Construct the global $context object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
		if(method_exists($this, 'init')){
			$this->init();
		}
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\AbstractBusiness
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Define the voName name for use in business logic
	 * @param string $class The value object class name
	 * @return  \Smally\AbstractBusiness
	 */
	public function setVoName($class){
		$this->_voName = $class;
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
	 * Get the value object class name
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	/**
	 * Return a new empty vo
	 * @return object
	 */
	public function getNew(){
		$className = $this->_voName;
		return new $className();
	}

	/**
	 * Return the factory of the smally app
	 * @return \Smally\Factory
	 */
	public function getFactory(){
		return $this->getApplication()->getFactory();
	}

	/**
	 * Return a generic Smally\Criteria of the business model or another one if $voName is define
	 * @param string $voName The voName of the criteria you want
	 * @return \Smally\Criteria
	 */
	public function getCriteria($voName=null){
		if(is_null($voName)) $voName = $this->getVoName();
		return $this->getFactory()->getCriteria($voName);
	}

	/**
	 * Return the Dao of the current business or a standard db dao if specific module dao doesn't exists (optional $voName for other vo Dao)
	 * @param  string $voName The voName of the dao you want
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao($voName=null){
		if(is_null($voName)) $voName = $this->getVoName();
		return $this->getFactory()->getDao($voName);
	}

	/**
	 * Return another business
	 * @param  string $voName The voName of the business you want
	 * @return \Smally\AbstractBusiness
	 */
	public function getBusiness($voName){
		if(is_null($voName)) throw new \Smally\Exception('You must request another Business');
		return $this->getFactory()->getBusiness($voName);
	}

	/**
	 * Return specific elements (vos) from the $criteria
	 * @param  \Smally\Criteria $addCriteria         An additionnal criteria to filter the data
	 * @return array
	 */
	public function fetchAll(\Smally\Criteria $criteria = null, $fetchVoName=null){
		if(!is_null($criteria) && is_null($criteria->getLimit())){
			$criteria->setLimit((int)(string)$this->getApplication()->getConfig()->smally->default->business->limit?:10);
		}
		return $this->getDao()->fetchAll($criteria, $fetchVoName);
	}

	/**
	 * Return a specific element (vo) from the $criteria
	 * @param  \Smally\Criteria $addCriteria         An additionnal criteria to filter the data
	 * @return \stdClass
	 */
	public function fetch(\Smally\Criteria $criteria = null, $fetchVoName=null){
		return $this->getDao()->fetch($criteria, $fetchVoName);
	}

	/**
	 * Return a specific element from an array key => values
	 * @param  array $array An array of key => values
	 * @return \stdClass
	 */
	public function exists($array){
		return $this->getDao()->exists($array);
	}

	/**
	 * Return a ValueObject by it's primary id, wrapper to dao
	 * @param  int $id               The id of the value object you want
	 * @return \stdClass
	 */
	public function getById($id,$force=false){
		return $this->getDao()->getById($id,$force);
	}

}