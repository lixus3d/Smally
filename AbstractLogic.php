<?php

namespace Smally;

class AbstractLogic {

	protected $_application = null;

	/**
	 * Must be define with module name for optimum functionnality
	 * @var string
	 */
	protected $_module = null;
	protected $_dao = null;

	/**
	 * Construct the global $context object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\AbstractLogic
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
	public function getCriteria($criteriaName=null){
		if(!is_null($criteriaName)){
			return $this->getFactory()->getCriteria($criteriaName);
		}else{
			return $this->getFactory()->getCriteria($this->_module);
		}
	}

	/**
	 * Return the Dao of the current module or a standard db dao if specific module dao doesn't exists
	 * @param  string $daoName A specific Dao name if you want
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao($daoName=null){

		if(!is_null($daoName)){
			return $this->getFactory()->getDao($daoName);
		}else{
			if(is_null($this->_dao)){
				$this->_dao = $this->getApplication()->getFactory()->getDao($this->_module);
			}
			return $this->_dao;
		}
	}

}