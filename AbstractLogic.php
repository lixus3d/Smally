<?php

namespace Smally;

class AbstractLogic {

	protected $_application = null;

	/**
	 * Must be define with module name for optimum functionnality
	 * @var string
	 */
	protected $_module = null;

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
	public function getCriteria($voName=null){
		return $this->getFactory()->getCriteria($voName);
	}

	/**
	 * Return the Dao of the current module or a standard db dao if specific module dao doesn't exists
	 * @param  string $daoName A specific Dao name if you want
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao($voName=null){
		return $this->getFactory()->getDao($voName);
	}

}