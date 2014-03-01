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

	/**
	 * Store every dbConnector object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_dbConnector = array(
			'default' => null,
		);

	/**
	 * Store every validator object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_validator = array();

	/**
	 * Store every filter object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_filter = array();

	/**
	 * Store every business object for reuse, not really a Singleton but close
	 * @var array
	 */
	protected $_business = array();

	protected $_criteriaPath = array();

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

	public function getView($templatePath){
		$view = new \Smally\View($this->getApplication());
		$view->setTemplatePath($templatePath);
		return $view;
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
			case 'VOMeta':
			case 'Filter':
			case 'Business':
			case 'Validator':
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
		// Return the default Dao if no voName given
		if(is_null($voName)) return $this->getDefaultDao();

		// Create the dao for this vo only if not present
		if(!isset($this->_dao[$voName])){

			$path = $this->getObjectPath($voName,'Dao');

			// If a precise Dao defined load it, else we get a default Dao
			if(class_exists($path)){
				$this->_dao[$voName] = new $path();
				// If the construct of the dao didn't define a connector, we place the default one
				if( is_null($this->_dao[$voName]->getConnector()) ){
					$this->_dao[$voName]->setConnector($this->getDefaultDbConnector());
				}
			}else{
				$this->_dao[$voName] = $this->getDefaultDao();
			}

			// Define some dao default var
			$this->_dao[$voName]
						->setVoName($voName)
						->setPrimaryKey($voName::PRIMARY_KEY) // Every VO must define a PRIMARY_KEY constant
						;

			if(method_exists($this->_dao[$voName],'init')){
				$this->_dao[$voName]->init();
			}
		}
		return $this->_dao[$voName];
	}

	/**
	 * Return a criteria object for a the given vo $voName
	 * @param  string $voName The vo name of the criteria object you want
	 * @return \Smally\Criteria
	 */
	public function getCriteria($voName=null){
		if(is_null($voName)) return $this->getDefaultCriteria();
		if(!isset($this->_criteriaPath[$voName])){
			$path = $this->getObjectPath($voName,'Criteria');
			if(!class_exists($path)){
				$path = '\\Smally\\Criteria';
			}
			$this->_criteriaPath[$voName] = $path;
		}
		$path = $this->_criteriaPath[$voName];
		return new $path();
	}

	/**
	 * Return a business object for a the given vo $voName
	 * @param  string $voName The vo name of the business object you want
	 * @return \Smally\AbstractBusiness
	 */
	public function getBusiness($voName=null){
		if(is_null($voName)) return null;
		if(!isset($this->_business[$voName])){
			$path = $this->getObjectPath($voName,'Business');
			if(class_exists($path)){
				$this->_business[$voName] = new $path($this->getApplication());
			}else{
				$this->_business[$voName] = new \Smally\AbstractBusiness($this->getApplication());
				$this->_business[$voName]->setVoName($voName); // Generic empty validator
			}
		}
		return $this->_business[$voName];
	}

	/**
	 * Return the default form of the given vo
	 * @param  string $voName The vo name of the form object you want
	 * @return \Smally\Form
	 */
	public function getForm($voName,$options=array()){
		$path = $this->getObjectPath($voName,'Form');
		if(class_exists($path)){
			return new $path($options);
		}else throw new Exception('Form doesn\'t exists : '.$path);
	}

	/**
	 * Return the default validator of the given vo
	 * @param  string $voName The vo name of the validator object you want
	 * @return \Smally\Validator
	 */
	public function getValidator($voName,$validatorMode=\Smally\Validator::MODE_NEW){
		if(is_null($voName)) return null;
		if(!isset($this->_validator[$voName])){
			$path = $this->getObjectPath($voName,'Validator');
			if(class_exists($path)){
				$this->_validator[$voName] = new $path(array(),$validatorMode);
			}else{
				$this->_validator[$voName] = new \Smally\Validator(array(),$validatorMode); // Generic empty validator
			}
		}
		return $this->_validator[$voName];
	}

	/**
	 * Return the default filter of the given vo
	 * @param  string $voName The vo name of the filter object you want
	 * @return \Smally\Filter
	 */
	public function getFilter($voName,$filterMode=\Smally\Filter::MODE_NEW){
		if(is_null($voName)) return null;
		if(!isset($this->_filter[$voName])){
			$path = $this->getObjectPath($voName,'Filter');
			if(class_exists($path)){
				$this->_filter[$voName] = new $path(array(),$filterMode);
			}else{
				$this->_filter[$voName] = new \Smally\Filter(array(),$filterMode); // Generic empty validator
			}
		}
		return $this->_filter[$voName];
	}

	/**
	 * Return the default filter of the given vo
	 * @param  string $voName The vo name of the filter object you want
	 * @return \Smally\Filter
	 */
	public function getVOMeta($voName){
		if(is_null($voName)) return null;
		if(!isset($this->_voMeta[$voName])){
			$path = $this->getObjectPath($voName,'VOMeta');
			if(class_exists($path)){
				$this->_voMeta[$voName] = new $path();
			}else throw new Exception('VOMeta doesn\'t exists for '.$voName);
		}
		return $this->_voMeta[$voName];
	}

	/**
	 * Return a Listing object for the given Value object $voName
	 * @param  string $voName Name of the vo you want to request
	 * @return \Smally\Listing
	 */
	public function getListing(){
		return new \Smally\Listing($this->getApplication());
	}

	/**
	 * Return a new Uploader object with the default upload path defined
	 * @return \Smally\Uploader
	 */
	public function getUploader(){
		$uploader = new \Smally\Uploader($this->getApplication());
		$uploader->setUploadPath( (string)$this->getApplication()->getConfig()->smally->upload->path?:ROOT_PATH.'public/data/' ) ;
		return $uploader;
	}

	/**
	 * Return a new Mailer object
	 * @return \Smally\Mailer
	 */
	public function getMailer(){
		$mailer = new \Smally\Mailer($this->getApplication());
		return $mailer;
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

			$this->_dbConnector['default'] = @new \Smally\Mysql($infos['host'],$infos['username'],$infos['password'],$infos['database'],$infos['port']);
			if($this->_dbConnector['default']->connect_error){
				throw new Exception("Can't connect to MySQL server");
			}
			$this->_dbConnector['default']->set_charset("utf8");

		}
		return $this->_dbConnector['default'];
	}


}