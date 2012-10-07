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
	public function getVoName(){
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
	 * Convert the class to an array representation ( recursive )
	 * @return array
	 */
	public function toArray(){
		$array = array();
		foreach($this as $key => $value){
			if(strpos($key,'_')===0) continue;
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
	 * GENERIC METHODS
	 */

	/**
	 * Generic wrapper to the makeControllerUrl and getBaseUrl from application that will give name and id of the current object
	 * @param  string $controllerPath The controller action you want
	 * @return string The absolute url of the controller action wanted
	 */
	public function getUrl($controllerPath){
		$params = array(
				'id' => $this->getId(),
				'name' => $this->getName(),
			);
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
	 * @param  string $field the field name
	 * @param  string $date  the given date in dd/mm/YYYY format
	 * @return \Smally\VO\Standard
	 */
	protected function _genericSetUts($field,$date){
		list($day,$month,$year) = explode('/',$date);
		$this->{$field} = mktime(0,0,0,$month,$day,$year);
		return $this;
	}

	/**
	 * Generic getter for uts field
	 * @param  string $field the field name
	 * @return string Date in dd/mm/YYYY format
	 */
	protected function _genericGetUts($field){
		return date('d/m/Y',$this->{$field});
	}

}