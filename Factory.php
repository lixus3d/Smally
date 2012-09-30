<?php

namespace Smally;

class Factory {

	protected $_application = null;

	/**
	 * Store every logic object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_logic = array();

	/**
	 * Store every dao object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_dao = array(
			'default' => null,
		);

	protected $_dbConnector = array(
			'default' => null,
		);

	/**
	 * Construct the factory object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Factory
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
	 * Compute a module path for objectType and check if the class exists
	 * @param  string $moduleName The module name
	 * @param  string $objectType The module object you want (Logic,Vo,Dao,etc ...)
	 * @return string
	 */
	public function getObjectPath($moduleName,$objectType){
		switch($objectType){
			case 'Logic':
				$fullName = $moduleName.'\\'.ucfirst($objectType);
				break;
			case 'Form':
			case 'Dao':
			case 'Criteria':
				$fullName = str_replace('\\VO\\','\\'.$objectType.'\\',$moduleName);
				break;
		}

		if(class_exists($fullName)){
			return $fullName;
		}
		return false;
	}

	/**
	 * Return a logic object for a given module $name
	 * @param  string $name The module name of the logic object you want
	 * @return \Smally\AbstractLogic
	 */
	public function getLogic($name){
		if(!isset($this->_logic[$name])){
			if($path = $this->getObjectPath($name,'Logic')){
				$this->_logic[$name] = new $path($this->getApplication());
			}else{
				throw new Exception('Invalid logic for module name : '.$name);
			}
		}
		return $this->_logic[$name];
	}

	/**
	 * Return a dao object for a given module $voName
	 * @param  string $voName The vo name of the dao object you want
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao($voName){
		if(is_null($voName)) return $this->getDefaultDao();
		if(!isset($this->_dao[$voName])){
			$path = $this->getObjectPath($voName,'Dao');
			if(class_exists($path)){
				$this->_dao[$voName] = new $path();
			}else{
				$this->_dao[$voName] = $this->getDefaultDao();
				$vo = new $voName(); // TODO : Find a better solution for getting table and primary key
				$this->_dao[$voName]
						->setValueObjectClass($voName)
						->setTable($vo->getTable())
						->setPrimaryKey($vo->getPrimaryKey())
						;
			}
		}
		return $this->_dao[$voName];
	}

	/**
	 * Return a criteria object for a the given vo $voName
	 * @param  string $voName The vo name of the criteria object you want
	 * @return \Smally\Criteria
	 */
	public function getCriteria($voName){
		if( !is_null($voName) && $path = $this->getObjectPath($voName,'Criteria')){
			if(class_exists($path)){
				return new $path();
			}
		}
		return $this->getDefaultCriteria();
	}

	/**
	 * Return a default Criteria object
	 * @return \Smally\Criteria
	 */
	public function getDefaultCriteria(){
		return new \Smally\Criteria();
	}

	/**
	 * Return a default Dao object
	 * @return \Smally\Dao\Db
	 */
	public function getDefaultDao(){
		$dao = new \Smally\Dao\Db();
		$dao->setConnector($this->getDefaultDbConnector());
		return $dao;
	}

	/**
	 * Return the default database connector
	 * @return \Smally\Mysql
	 */
	public function getDefaultDbConnector(){
		if(!isset($this->_dbConnector['default'])){

			$defaultInfos = array(
					'host' => '127.0.0.1',
					'port' => 3306,
					'username' => 'root',
					'password' => '',
					'database' => 'smally',
				);
			$infos = array_merge($defaultInfos,$this->getApplication()->getConfig()->smally->db->toArray());

			$this->_dbConnector['default'] = new \Smally\Mysql($infos['host'],$infos['username'],$infos['password'],$infos['database'],$infos['port']);

		}
		return $this->_dbConnector['default'];
	}


}