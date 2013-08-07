<?php

namespace Smally\VO;

/**
 * The default value object, every vo must extends this class
 */

class Standard extends \stdClass {

	protected $_voExtend = null;
	protected $_noVoExtend = false;

	protected $_voName = null;
	protected $_table = null;
	protected $_primaryKey = null;
	protected $_nameKey = 'name';
	protected $_searchFields = null;

	protected $_application = null;
	protected $_factory = null;
	protected $_dao = null;
	protected $_business = null;

	protected $_logger = null;


	/**
	 * Init a value object with $vars
	 * @param array $vars array of $property => $value of the value object
	 */
	public function __construct($vars=array()){
		$this->loadExtend();
		$this->initVars($vars);
	}


	/**
	 * Generic toString do a getName()
	 * @return string
	 */
	public function __toString(){
		return $this->getName();
	}

	public function __sleep(){
		$export = array();
		foreach($this as $propertyName => $value){
			if(strpos($propertyName, '_')===0) continue;
			$export[] = $propertyName;
		}
		return $export;
	}

	/**
	 * Tricky way to add functions to a vo class, usefull to regenerate base skeleton without erasing some vo specific methods
	 * @return \Smally\VO\Standard
	 */
	public function loadExtend(){
		$className = $this->getVoName(true);
		$className = str_replace('\\VO\\','\\VOExtend\\',$className);
		if(class_exists($className)){
			$this->_voExtend = new $className($this);
		}else{
			$this->_noVoExtend = true;
		}
		return $this;
	}

	public function getVoExtend(){
		if(is_null($this->_voExtend)&&$this->_noVoExtend!==true){
			$this->loadExtend();
		}
		return $this->_voExtend;
	}

	/**
	 * Automatically call extender method if present, exception otherwise
	 * @return mixed
	 */
	public function __call($name,$args){
		if($this->getVoExtend() instanceof Extender){
			if(method_exists($this->_voExtend, $name)){
				return call_user_func_array(array($this->_voExtend,$name),$args);
			}
		}
		throw new \Exception('Method '.$name.' doesn\'t exist in current vo.');
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
			}elseif($name !== 'submitter'){
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
	 * Return the appropriate business for the current value object , default business otherwise
	 * @return \Smally\AbstractBusiness
	 */
	public function getBusiness(){
		if(is_null($this->_business)){
			$this->_business = $this->getFactory()->getBusiness(get_called_class());
		}
		return $this->_business;
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

	public function getModule(){
		return substr($this->getVoName(true),0,strpos($this->getVoName(true),'\\'));
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
	public function toArray($withGetter=true,$withPrimaryKey=true){
		$array = array();
		foreach($this as $key => $value){
			if(strpos($key,'_')===0) continue; // we did not export _protected values
			if(!$withPrimaryKey&&$key==$this->getPrimaryKey()) continue;
			$method = 'get'.ucfirst($key);
			if($withGetter && method_exists($this, $method)){
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
	public function getUrl($controllerPath,$params=array(),$replace=false){
		if(!$replace){
			$defaultParams = array(
					'id' => $this->getId(),
					'name' => $this->getName(),
				);
			$params = array_merge($defaultParams,$params);
		}
		$url = $this->getApplication()->makeControllerUrl($controllerPath,$params);
		return $this->getApplication()->getBaseUrl($url);
	}

	/**
	 * Quick access to dao store method
	 * @return boolean true if store succeded
	 */
	public function store(){
		return $this->getDao()->store($this);
	}

	/**
	 * Quick access to dao delete method
	 * @return boolean true if delete succeded
	 */
	public function delete(){
		return $this->getDao()->delete($this);
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
		if(is_array($this->_nameKey)){
			$name = array();
			foreach($this->_nameKey as $partKey){
				$name[] = (string) $this->{$partKey};
			}
			return implode(' ',$name);
		}elseif(isset($this->{$this->_nameKey})){
			return (string) $this->{$this->_nameKey};
		}else return '';
	}

	/**
	 * Generic setter for uts field
	 * @param  string $fieldName the field name
	 * @param  string $date  the given date in dd/mm/YYYY format
	 * @return \Smally\VO\Standard
	 */
	protected function _genericSetUts($fieldName,$date){
		if(strpos($date, '/') !== false){
			list($day,$month,$year) = explode('/',$date);
			$this->{$fieldName} = mktime(0,0,0,$month,$day,$year);
		}
		return $this;
	}

	/**
	 * Generic getter for uts field
	 * @param  string $fieldName the field name
	 * @return string Date in dd/mm/YYYY format by default
	 */
	protected function _genericGetUts($fieldName,$format='d/m/Y'){
		return $this->{$fieldName} >= 1 ? date($format,$this->{$fieldName}) : null ;
	}

	/**
	 * Generic setter for tag field
	 * @param  string $fieldName The field name
	 * @param  string $tags      The list of tag names you want to set
	 * @param  string $voName    The vo name of the tag
	 * @param  string $nameField The name field in the tag vo
	 * @return \Smally\VO\Standard
	 */
	protected function _genericSetTag($fieldName, $tags, $voName, $nameField='name'){

		$this->{$fieldName} = array();
		$tags = explode(',',$tags);
		foreach($tags as $k => $tag){
			$tags[$k] = trim($tag);
			if($tag=='') unset($tags[$k]);
		}

		if($tags){
			$dao = $this->getFactory()->getDao($voName);
			$criteria = $dao->getCriteria();
			$criteria ->setFilter(array($nameField=>array('value'=>$tags,'operator'=>'IN')));

			$existingTags = array();
			if($tagList = $dao->fetchAll($criteria)){
				foreach($tagList as $tagVo){
					$existingTags[$tagVo->getId()] = $tagVo->getName();
				}
			}

			foreach($tags as $tag){
				if(in_array($tag, $existingTags)){
					$this->{$fieldName}[] = array_search($tag, $existingTags);
				}else{
					$vo = new $voName();
					$vo->name = $tag;
					if($dao->store($vo)){
						$this->{$fieldName}[] = $vo->getId();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Generic storer for joint between two VO (many to many relation)
	 * @param  string $fieldName The field that contains id
	 * @param  string $jVoName   The voName of the joint table
	 * @param  array  $jointVars The values to have in the joint table in addition of the $fieldName key => value pair
	 * @param  string $destinationFieldName The fieldName in the join table for the given $fieldName (usefull for multi joint table like j_upload)
	 * @return \Smally\Vo\Standard
	 */
	protected function _genericStoreJointModelId($fieldName,$jVoName=null,$jointVars=null,$destinationFieldName=null){

		if(is_null($destinationFieldName)) $destinationFieldName = $fieldName;

		// We try to determine a valid jVoName for the fieldName
		if(is_null($jVoName)){
			$jVoName = $this->getModule().'\\VO\\'.'j'.(ucfirst(str_replace('Id','',$fieldName))).$this->getVoName();
		}

		$jDao = $this->getApplication()->getFactory()->getDao($jVoName);

		// default vars for the joint Vo / table , usually the current model primaryKey, key => value pair
		if(is_null($jointVars)){
			$jointVars = array(
				$this->getPrimaryKey() => $this->getId(),
			);
		}

		$getterName = 'get'.ucfirst($fieldName);
		if(method_exists($this, $getterName)){
			$modelIdList = $this->{$getterName}();
		}else $modelIdList = $this->{$fieldName};

		$inBaseIdList = $this->_genericGetJointModelId($fieldName,$jVoName,$jointVars);

		// We update/insert joints
		foreach($modelIdList as $ord => $modelId){
			$vars = array_merge($jointVars,array($destinationFieldName => $modelId));

			if(!($jObject = $jDao->exists($vars))){
				$jObject = new $jVoName($vars);
			}

			if(property_exists($jObject, 'ord')){
				$jObject->ord = $ord;
			}

			$jDao->store($jObject);
			if(in_array($modelId,$inBaseIdList)){
				unset($inBaseIdList[array_search($modelId, $inBaseIdList)]);
			}
		}

		// We delete joints that we didn't found in the field
		foreach($inBaseIdList as $modelId){

			$vars = array_merge($jointVars,array($destinationFieldName => $modelId));

			if($jObject = $jDao->exists($vars)) {
				$jDao->delete($jObject);
			}
		}

		return $this;

	}


	/**
	 * Generic storer for file/upload field (Quick wrapper to the _genericStoreModelId)
	 * @param  string $fieldName    the field name
	 * @return \Smally\VO\Standard
	 */
	protected function _genericStoreUploadId($fieldName){
		return $this->_genericStoreJointModelId($fieldName,'\\Smally\\VO\\jUpload',array('voName' => $this->getVoName(true),'voId' => $this->getId(),'voField'=>$fieldName),'uploadId');
	}

	/**
	 * Generic many to many relation getter for id list
	 * @param  string $fieldName            The field that contains id
	 * @param  string $jVoName              The voName of the joint table
	 * @param  array $jointVarsFilter       The filter of values to have in the joint table
	 * @param  array $orderFilter          	The order array for the joint dao
	 * @param  string $destinationFieldName The fieldname of the joint table if different from $fieldName
	 * @return array
	 */
	protected function _genericGetJointModelId($fieldName,$jVoName=null,$jointVarsFilter=null,$orderFilter=null,$destinationFieldName=null){

		if(is_null($destinationFieldName)) $destinationFieldName = $fieldName;

		$idList = array();

		// We try to determine a valid jVoName for the fieldName
		if(is_null($jVoName)){
			$jVoName = $this->getModule().'\\VO\\'.'j'.(ucfirst(str_replace('Id','',$fieldName))).$this->getVoName();
		}

		$jDao = $this->getApplication()->getFactory()->getDao($jVoName);

		// default vars for the joint Vo / table , usually the current model primaryKey, key => value pair
		if(is_null($jointVarsFilter)){
			$jointVarsFilter = array(
				$this->getPrimaryKey() => array('value'=>$this->getId()),
			);
		}else{
			foreach($jointVarsFilter as $key => &$value){
				$value = array('value'=>$value);
			}
		}

		$criteria = $this->getApplication()->getFactory()->getCriteria($jVoName);
		$criteria->setFilter($jointVarsFilter);

		if($orderFilter){
			$criteria->setOrder($orderFilter);
		}

		if($results = $jDao->fetchAll($criteria)){
			foreach($results as $joint){
				$idList[]= (int) $joint->{$destinationFieldName};
			}
		}

		return $idList;
	}

	/**
	 * Get uploadId list of the given fieldName for the current model
	 * @param  string $fieldName The fieldName to get uploadId from
	 * @return array
	 */
	protected function _genericGetUploadId($fieldName){
		$filter = array(
							'voName' => $this->getVoName(true),
							'voId' => $this->getId(),
							'voField' => $fieldName,
						);
		$order = array(array('ord','ASC'));
		return $this->_genericGetJointModelId($fieldName, '\\Smally\\VO\\jUpload', $filter, $order, 'uploadId');
	}

	/**
	 * Generic method to get all VO for a given join $fieldName
	 * @param  string $fieldName            The field that contains id
	 * @param  string $voName    voName of the subvo
	 * @return array()
	 */
	protected function _genericGetJointModel($fieldName,$voName=null){

		if(is_null($voName)) $voName = '\\'.$this->getModule().'\\VO\\'.(ucfirst(str_replace('Id','',$fieldName)));

		$getterName = 'get'.ucfirst($fieldName);
		if(method_exists($this, $getterName)){
			$idList = $this->{$getterName}();
		}else $idList = $this->_genericGetJointModelId($fieldName);

		$voList = array();
		if($idList){
			$voDao = $this->getApplication()->getFactory()->getDao($voName);
			$idToLoad = array();
			foreach($idList as $id){
				if($vo = $voDao->getByIdCache($id)){
					$voList[] = $vo;
				}else{
					$idToLoad[] = $id;
				}
			}
			if($idToLoad){
				$criteria = $voDao->getCriteria();
				$criteria->setFilter(array($fieldName => array('value' => $idToLoad)));
				if($voLoad = $voDao->fetchAll($criteria)){
					foreach($voLoad as $vo){
						$voList[] = $vo;
					}
				}
			}
		}
		return $voList;
	}

	/**
	 * Generic method to get all Upload VO for the given $fieldName of the current model
	 * @param  string $fieldName The fieldName to get upload Vo from
	 * @todo  Try to include in _genericGetModel
	 * @return array
	 */
	protected function _genericGetUpload($fieldName){
		$getterName = 'get'.ucfirst($fieldName);
		if(method_exists($this, $getterName)){
			$idList = $this->{$getterName}();
		}else $idList = $this->_genericGetUploadId($fieldName);

		$uploadVoList = array();
		if($idList){
			$uploadVoName = '\\Smally\\VO\\Upload';
			$uploadDao = $this->getApplication()->getFactory()->getDao($uploadVoName);
			// $criteria = $uploadDao->getCriteria();
			// $criteria->setFilter(array('uploadId'=>array('value'=>$idList)));
			// $uploadVoList = $uploadDao->fetchAll($criteria);
			foreach($idList as $id){
				if($upload = $uploadDao->getById($id)){
					$uploadVoList[] = $upload;
				}
			}
		}
		return $uploadVoList;
	}

	/**
	 * Generic method to set submodel values for the given $fieldName of the current model
	 * @param  string $fieldName The fieldName to set the values for
	 * @param  array $values    Array of lines, each lines containing values
	 * @return array
	 */
	protected function _genericSetSubmodel($fieldName,$values){
		$return = array();
		if(is_array($values)){
			foreach($values as $k => $value){
				$ok = false;
				foreach($value as $field => $v){
					if( $v !== '' && $v !== '0:0' ){ // 0:0 for select / radio default choice
						$ok = true;
						break;
					}
				}
				if($ok){
					$return[] = $value;
				}
			}
		}
		return $return;
	}

	/**
	 * Generic method to get submodel VO for the given field
	 * @param  string $fieldName The fieldName to get the Vo
	 * @param  string $voName    The vo of the submodel
	 * @param  boolean $shared   Is the submodel table shared for multiple parent vo
	 * @return array
	 */
	protected function _genericGetSubmodel($fieldName,$voName,$shared=false){
		$results = array();
		if($this->getId()){
			$dao = $this->getFactory()->getDao($voName);
			$criteria = $dao->getCriteria();
			if($shared){
				$criteria->setFilter(array(
						'voName' => array('value'=>$this->getVoName(true)),
						'voId' => array('value'=>$this->getId()),
					));
			}else{
				$criteria->setFilter(array($this->getPrimaryKey()=>array('value'=>$this->getId())));
			}
			if($list = $dao->fetchAll($criteria)){
				foreach($list as $submodel){
					$results[] = $submodel->toArray();
				}
			}
		}
		return $results;
	}

	/**
	 * Generic method to store a submodel for a given field
	 * @param  string $fieldName The fieldName to store the Vo from
	 * @param  string $voName    The vo of the submodel
	 * @param  boolean $shared   Is the submodel table shared for multiple parent vo
	 * @return null
	 */
	protected function _genericStoreSubmodel($fieldName,$voName,$shared=false){

		$getterName = 'get'.ucfirst($fieldName);
		if(method_exists($this, $getterName)){
			$values = $this->{$getterName}();
		}else $values = $this->{$fieldName};

		foreach($values as $k => $vars){
			$vo = new $voName($vars);
			if($shared){
				$vo->voName = $this->getVoName(true);
				$vo->voId = $this->getId();
			}else{
				$vo->{$this->getPrimaryKey()} = $this->getId();
			}
			$vo->getDao()->store($vo);
		}

		return $this;
	}

}