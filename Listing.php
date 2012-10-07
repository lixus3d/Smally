<?php

namespace Smally;

class Listing {

	protected $_application = null;

	protected $_voName = null;
	protected $_paging = null;
	protected $_criteria = null;

	protected $_nbItems = null;


	/**
	 * Construct a new Listing
	 * @param string $voName Name of the value object to request
	 */
	public function __construct(\Smally\Application $application, $voName=null){
		$this->setApplication($application);
		$this->setVoName($voName);
	}

	/**
	 * Define a back reference to the application
	 * @param \Smally\Application $application
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Set the value object of the
	 * @param string $voName Name of the value object to request
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	/**
	 * Set the criteria for the request
	 * @param \Smally\Criteria $criteria a valid criteria
	 */
	public function setCriteria(\Smally\Criteria $criteria){
		$this->_criteria = $criteria;
		return $this;
	}

	/**
	 * Set the paging object
	 * @param \Smally\Paging $paging
	 */
	public function setPaging(\Smally\Paging $paging){
		$this->_paging = $paging;
		return $this;
	}

	/**
	 * Set the total number of items in the last request
	 * @param int $nbItems The total number of items of the last request
	 * @return \Smally\Listing
	 */
	public function setNbItems($nbItems){
		$this->_nbItems = (int) $nbItems;
		return $this;
	}

	/**
	 * Return the name of the value object to request
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	/**
	 * Return the criteria of the request
	 * @return \Smally\Criteria
	 */
	public function getCriteria(){
		return $this->_criteria;
	}

	/**
	 * Return the back reference of the application
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * Return the factory of the application
	 * @return \Smally\Factory
	 */
	public function getFactory(){
		return $this->getApplication()->getFactory();
	}

	/**
	 * Return the paging object, create it the first time
	 * @return \Smally\Paging
	 */
	public function getPaging(){
		if(is_null($this->_paging)){
			$this->_paging = new \Smally\Helper\Paging();
			$this->_paging
						->setNbItems($this->getNbItems())
						->setPage()
						;
		}
		return $this->_paging;
	}

	/**
	 * Return the total number of items in the last request
	 * @return int
	 */
	public function getNbItems(){
		return $this->_nbItems;
	}

	/**
	 * Return the results of the Listing
	 * @return array Array of \Smally\VO\Standard
	 */
	public function fetchAll(){

		// We first request the dao to know the total number of items
		$this->setNbItems( $this->getFactory()
									->getDao($this->getVoName())
										->fetchCount($this->getCriteria())
										);

		// We init the final criteria with the paging interval
		$this->getCriteria()->setLimit($this->getPaging()->getInterval());

		// we request the dao for the final items set with interval filter
		return $this->getFactory()
						->getDao($this->getVoName())
							->fetchAll($this->getCriteria());
	}

}