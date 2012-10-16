<?php

namespace Smally\VO;

/**
 * The default value object, every vo must extends this class
 */

class Standard extends \stdClass {

	protected $_voName = null;
	protected $_table = null;
	protected $_primaryKey = null;
	protected $_nameKey = 'name';
	protected $_searchFields = null;

	protected $_application = null;
	protected $_factory = null;
	protected $_dao = null;

	protected $_logger = null;

	/**
	 * Init a value object with $vars
	 * @param array $vars array of $property => $value of the value object
	 */
	public function __construct($vars=array()){
		$this->initVars($vars);
	}

	/**
	 * Overwrite any existing property with the value in $vars
	 * @param  array $vars array of $property => $value of the value object
	 * @return \Smally\VO\Standard
	 */
	public function initVars(array $vars){
		foreach($vars as $name => $value){
			$method = 'set'.ucfirst($name);
			if(method_exists($this, $method)){
				$this->{$method}($value);
			}else if(property_exists($this, $name)){
				$this->{$name} = $value;
			}else{
				\Smally\Logger::getInstance()->log('Trying to set a non declared propery of '.$this->getVoName().' : '.((string)$name),\Smally\Logger::LVL_WARNING);
			}
		}
		return $this;
	}

	/**
	 * Return the current application instance, store it the first time called
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	/**
	 * Return the appropriate dao for the current value object , default dao is database + mysqli
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao(){
		if(is_null($this->_dao)){
			$this->_dao = $this->getFactory()->getDao(get_called_class());
		}
		return $this->_dao;
	}

	/**
	 * Return the factory for the current execution
	 * @return \Smally\Factory
	 */
	public function getFactory(){
		if(is_null($this->_factory)){
			$this->_factory = $this->getApplication()->getFactory();
		}
		return $this->_factory;
	}

	/**
	 * Return the Value Object name ( pure , without namespace )
	 * @return string
	 */
	public function getVoName($complete=false){
		if($complete) return get_called_class();
		if(is_null($this->_voName)){
			$this->_voName = substr(strrchr(get_called_class(),'\\'),1);
		}
		return $this->_voName;
	}

	/**
	 * Return the table name for the current value object, generic is a lowercase version of vo name with '_' before each uppercase letter
	 * @example ArticleTag will become article_tag , if you extends the standard value object you can define a specific table just by defining the $_table property
	 * @return string
	 */
	public function getTable(){
		if(is_null($this->_table)){
			$this->_table = trim(preg_replace('#([A-Z])#e',"strtolower('_\\1')",$this->getVoName()),'_');
		}
		return $this->_table;
	}

	/**
	 * Return the primary key of the given value object, default is table name with 'Id' suffix
	 * @example Article VO will have the primaryKey articleId , if you extends the standard value object you can define a specific primaryKey just by defining the $_primaryKey property
	 * @return string
	 */
	public function getPrimaryKey(){
		if(is_null($this->_primaryKey)){
			$this->_primaryKey = $this->getTable().'Id';
		}
		return $this->_primaryKey;
	}

	/**
	 * Return the default fields for a search
	 * @return array
	 */
	public function getSearchFields(){
		return $this->_searchFields;
	}

	/**
	 * Return the application logger and store it for future uses
	 * @return \Smally\Logger
	 */
	public function getLogger(){
		if(is_null($this->_logger)){
			$this->_logger = $this->getApplication()->getLogger();
		}
		return $this->_logger;
	}

	/**
	 * Convert the class to an array representation ( recursive )
	 * @return array
	 */
	public function toArray(){
		$array = array();
		foreach($this as $key => $value){
			if(strpos($key,'_')===0) continue; // we did not export _protected values
			$method = 'get'.ucfirst($key);
			if(method_exists($this, $method)){
				$array[$key] = $this->{$method}();
 			}else{
				$array[$key] = $value;
			}
		}
		return $array;
	}

	/**
	 * Easily log something with this \Smally\Logger->log() shortcut
	 * @param  string $text        The text to log , array are converted with print_r
	 * @param  int $level       the level of the text to log
	 * @param  int $destination destination of the log, bitfield so you can log to multiple destination
	 * @return boolean
	 */
	public function log($text='',$level=\Smally\Logger::LVL_INFO,$destination=\Smally\Logger::DEST_LOG){
		return $this->getLogger()->log($text,$level,$destination);
	}

	/**
	 * GENERIC METHODS
	 */

	/**
	 * Generic wrapper to the makeControllerUrl and getBaseUrl from application that will give name and id of the current object
	 * @param  string $controllerPath The controller action you want
	 * @return string The absolute url of the controller action wanted
	 */
	public function getUrl($controllerPath,$params=array()){
		$defaultParams = array(
				'id' => $this->getId(),
				'name' => $this->getName(),
			);
		$params = array_merge($defaultParams,$params);
		$url = $this->getApplication()->makeControllerUrl($controllerPath,$params);
		return $this->getApplication()->getBaseUrl($url);
	}

	/**
	 * GENERIC GETTER AND SETTER FOR USUAL PROPERTY FORMAT
	 */

	/**
	 * Generic method that will return the primaryId of the vo
	 * @return int
	 */
	public function getId(){
		return $this->{$this->getPrimaryKey()};
	}

	/**
	 * Generic method that will return the name of the vo
	 * @return string
	 */
	public function getName(){
		return $this->{$this->_nameKey};
	}

	/**
	 * Generic setter for uts field
	 * @param  string $fieldName the field name
	 * @param  string $date  the given date in dd/mm/YYYY format
	 * @return \Smally\VO\Standard
	 */
	protected function _genericSetUts($fieldName,$date){
		list($day,$month,$year) = explode('/',$date);
		$this->{$fieldName} = mktime(0,0,0,$month,$day,$year);
		return $this;
	}

	/**
	 * Generic getter for uts field
	 * @param  string $fieldName the field name
	 * @return string Date in dd/mm/YYYY format
	 */
	protected function _genericGetUts($fieldName){
		return date('d/m/Y',$this->{$fieldName});
	}

	/**
	 * Generic storer for file/upload field
	 * @param  string $fieldName    the field name
	 * @param  array  $uploadIdList An array of uploadId you want to associate
	 * @return \Smally\VO\Standard
	 */
	protected function _genericStoreUpload($fieldName){

		$uploadIdList = $this->{$fieldName};
		$inBaseIdList = $this->_genericGetUpload($fieldName);

		$jVoName = '\\Smally\\VO\\jUpload';
		$jUploadDao = $this->getApplication()->getFactory()->getDao($jVoName);

		// We update/insert uploads
		foreach($uploadIdList as $ord => $uploadId){

			$vars = array(
				'uploadId' => $uploadId,
				'voName' => $this->getVoName(true),
				'voId' => $this->getId()
			);

			if(!($jObject = $jUploadDao->exists($vars))){
				$jObject = new $jVoName($vars);
			}

			$jObject->ord = $ord;

			$jUploadDao->store($jObject);
			if(in_array($uploadId,$inBaseIdList)){
				unset($inBaseIdList[array_search($uploadId, $inBaseIdList)]);
			}
		}

		// We delete uploads that we didn't found in the field
		foreach($inBaseIdList as $uploadId){
			$vars = array(
				'uploadId' => $uploadId,
				'voName' => $this->getVoName(true),
				'voId' => $this->getId()
			);
			if($jObject = $jUploadDao->exists($vars)) {
				$jUploadDao->delete($jObject);
			}
		}

		return $this;
	}

	protected function _genericGetUpload($fieldName){
		$idList = array();

		$jVoName = '\\Smally\\VO\\jUpload';

		$jUploadDao = $this->getApplication()->getFactory()->getDao($jVoName);

		$criteria = $this->getApplication()->getFactory()->getCriteria($jVoName);
		$criteria->setFilter(array(
								'voName' => array('value'=>$this->getVoName(true)),
								'voId' => array('value'=>$this->getId())
						))
					->setOrder(array(array('ord','ASC')))
					;

		if($results = $jUploadDao->fetchAll($criteria)){
			foreach($results as $joint){
				$idList[]= (int) $joint->uploadId;
			}
		}

		return $idList;
	}

}