<?php

namespace Smally;

abstract class AbstractBootstrap {

	protected $_application = null;

	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;

	}
	public function getApplication(){
		return $this->_application;
	}

	abstract public function x();

}