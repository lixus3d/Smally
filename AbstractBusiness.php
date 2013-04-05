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
	 * Return the factory of the smally app
	 * @return \Smally\Factory
	 */
	public function getFactory(){
		return $this->getApplication()->getFactory();
	}

	/**
	 * Return a generic Smally\Criteria
	 * @return \Smally\Criteria
	 */
	public function getCriteria($voName=null){
		if(is_null($voName)) $voName = $this->getVoName();
		return $this->getFactory()->getCriteria($voName);
	}

	/**
	 * Return the Dao of the current module or a standard db dao if specific module dao doesn't exists
	 * @param  string $daoName A specific Dao name if you want
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao($voName=null){
		if(is_null($voName)) $voName = $this->getVoName();
		return $this->getFactory()->getDao($voName);
	}

	/**
	 * Return specific elements (vos) from the $criteria
	 * @param  \Smally\Criteria $addCriteria         An additionnal criteria to filter the data
	 * @return array
	 */
	public function fetchAll(\Smally\Criteria $addCriteria = null){
		$criteria = $this->getCriteria($voName)->setLimit(10);
		if($addCriteria) $criteria->import($addCriteria);
		return $this->getDao()->fetchAll($criteria);
	}

	/**
	 * Return a specific element (vo) from the $criteria
	 * @param  \Smally\Criteria $addCriteria         An additionnal criteria to filter the data
	 * @return \stdClass
	 */
	public function fetch(\Smally\Criteria $addCriteria = null){
		$criteria = $this->getCriteria($voName)->setLimit(10);
		if($addCriteria) $criteria->import($addCriteria);
		return $this->getDao()->fetch($criteria);
	}

}