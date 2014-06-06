<?php

namespace Smally;

class Saver {

	protected $_voName = null;

	protected $_controller = null;
	protected $_form = null;
	protected $_formPrefix = null;
	protected $_dao = null;
	protected $_validator = null;
	protected $_filter = null;
	protected $_inputs = null;
	protected $_defaultValues = null;
	protected $_allowedFieldsCreate = null;
	protected $_allowedFieldsUpdate = null;
	protected $_redirect = null;
	protected $_autoValues = null;
	protected $_vo = null;

	protected $_partEdit = false;
	protected $_mode = null;

	protected $_storeState = null;
	protected $_errors = null;

	protected $_urlParamId = 'id';
	protected $_urlParamCopyId = 'copyId';
	protected $_formParamSubmitter = 'submitter';

	protected $_application = null;

	public function __construct($voName=null){
		$this->setVoName($voName);
	}

	/**
	 * Define the voName of the Saver
	 * @param string $voName The vo name you want to manipulate
	 * @return \Smally\Saver
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	/**
	 * Force a particular form, default form is auto loaded from _voName
	 * @param \Smally\Form $form A form you want to force to use
	 * @return \Smally\Saver
	 */
	public function setForm(\Smally\Form $form){
		$this->_form = $form;
		return $this;
	}

	/**
	 * Define the callingController if relevant
	 * @param \Smally\Controller $controller The calling controller of the Saver
	 * @return \Smally\Saver
	 */
	public function setController(\Smally\Controller $controller){
		$this->_controller = $controller;
		return $this;
	}

	/**
	 * Define the defaultValues to use in the form
	 * @param array $defaultValues An array of key values to put by defaults
	 * @return \Smally\Saver
	 */
	public function setDefaultValues($defaultValues){
		$this->_defaultValues = $defaultValues;
		return $this;
	}

	/**
	 * Define the fields that will be allowed in the inputs
	 * @param array $createFields An array of fields name
	 * @param array $updateFields An array of fields name or null if you want the same as $createFields
	 * @return \Smally\Saver
	 */
	public function setAllowedFields($createFields, $updateFields=null){
		$this->_allowedFieldsCreate = $createFields;
		$this->_allowedFieldsUpdate = !is_null($updateFields)?$updateFields:$createFields;
		return $this;
	}

	/**
	 * Force a specific form prefix
	 * @param string $formPrefix The form prefix you want to use
	 * @return \Smally\Saver
	 */
	public function setFormPrefix($formPrefix){
		$this->_formPrefix = $formPrefix;
		return $this;
	}

	/**
	 * Define a specific validator for the inputs, default is auto loaded from _voName
	 * @param \Smally\Validator $validator A particular validator you want to use
	 * @return \Smally\Saver
	 */
	public function setValidator(\Smally\Validator $validator){
		$this->_validator = $validator;
		return $this;
	}


	/**
	 * Define a specific filter for the inputs, default is auto loaded from _voName
	 * @param \Smally\Filter $filter A particular filter you want to use
	 * @return \Smally\Saver
	 */
	public function setFilter(\Smally\Filter $filter){
		$this->_filter = $filter;
		return $this;
	}

	/**
	 * Define a redirect url you want to go to after storing success
	 * @param string $redirect A valid url
	 * @return \Smally\Saver
	 */
	public function setRedirect($redirect){
		$this->_redirect = $redirect;
		return $this;
	}

	/**
	 * Change the default url param names
	 * @param string $name  The name of the url param you want to change name
	 * @param string $value The new name of the param
	 * @return \Smally\Saver
	 */
	public function setUrlParam($name,$value){
		$this->{'_urlParam'.ucfirst($name)} = $value;
		return $this;
	}

	/**
	 * Change the default form param names
	 * @param string $name  The name of the form param you want to change name
	 * @param string $value The new name of the param
	 * @return \Smally\Saver
	 */
	public function setFormParam($name,$value){
		$this->{'_formParam'.ucfirst($name)} = $value;
		return $this;
	}

	/**
	 * Define some auto values you want to push to the vo if present
	 * @param array $autoValues An array of key => values you want to set in the vo if key present
	 * @return \Smally\Saver
	 */
	public function setAutoValues($autoValues){
		$this->_autoValues = $autoValues;
		return $this;
	}

	/**
	 * Set/Unset the saver in part edit mode
	 * @param boolean $state State you want for the part edit mode
	 * @return \Smally\Saver
	 */
	public function setPartEdit($state){
		$this->_partEdit = $state?true:false;
		return $this;
	}

	public function setVo($vo){
		if( get_class($vo) == $this->_voName){
			$this->_vo = $vo;
		}
		return $this;
	}

	/**
	 * Return the current smally app
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
	}

	/**
	 * Return the saver form, default is _voName default form if no form define by setForm
	 * @return \Smally\Form
	 */
	public function getForm(){
		if( is_null($this->_form) && $this->_voName ){
			$options = array();
			if($this->getVo()->getId()){
				$options['actualVo'] = $this->getVo();
			}
			$this->_form =  $this->getApplication()->getFactory()->getForm($this->_voName,$options); // create the good form for the $vo
		}
		return $this->_form;
	}

	/**
	 * Return the calling controller if define by setController
	 * @return \Smally\Controller
	 */
	public function getController(){
		return $this->_controller;
	}

	/**
	 * Return the defaultValues for the saver. Either define by user or loaded from context
	 * @return array
	 */
	public function getDefaultValues(){
		return $this->_defaultValues?:$this->getApplication()->getContext()->default->toArray();
	}

	/**
	 * Return the alloweds fields during a create/add saver
	 * @return array Return null if no allowedfields has been defined
	 */
	public function getAllowedFieldsCreate(){
		return $this->_allowedFieldsCreate;
	}

	/**
	 * Return the alloweds fields during a update/edit saver
	 * @return array Return null if no allowedfields has been defined
	 */
	public function getAllowedFieldsUpdate(){
		return $this->_allowedFieldsUpdate;
	}

	/**
	 * Return the form prefix, default is computed from _voName
	 * @return string
	 */
	public function getFormPrefix(){
		if(is_null($this->_formPrefix) && $this->_voName){
			$this->_formPrefix = substr(strrchr($this->_voName,'\\'),1);
		}
		return $this->_formPrefix;
	}

	/**
	 * Return the _voName Dao
	 * @return \Smally\Dao\InterfaceDao
	 */
	public function getDao(){
		if(is_null($this->_dao)){
			$this->_dao = $this->getApplication()->getFactory()->getDao($this->_voName);
		}
		return $this->_dao;
	}

	/**
	 * Return the redirect
	 * @return string
	 */
	public function getRedirect(){
		return $this->_redirect;
	}

	/**
	 * Get the validator defined, or autoload the generic one for the _voName
	 * @return \Smally\Validator
	 */
	public function getValidator(){
		if(is_null($this->_validator) && $this->_voName){
			$this->_validator = $this->getApplication()->getFactory()->getValidator($this->_voName);
		}
		return $this->_validator;
	}

	/**
	 * Get the filter defined, or autoload the generic one for the _voName
	 * @return \Smally\Filter
	 */
	public function getFilter(){
		if(is_null($this->_filter) && $this->_voName){
			$this->_filter = $this->getApplication()->getFactory()->getFilter($this->_voName);
		}
		return $this->_filter;
	}

	/**
	 * Get the current inputs for the saver
	 * @return array
	 */
	public function getInputs(){
		if(is_null($this->_inputs) && $formPrefix = $this->getFormPrefix()){

			$inputs = $this->getApplication()->getContext()->{$formPrefix}->toArray();

			$testFields = null;
			switch($this->getMode()){
				case 'add':
					$testFields = $this->getAllowedFieldsCreate();
					break;
				case 'edit':
					$testFields = $this->getAllowedFieldsUpdate();
					break;
			}
			if(!is_null($testFields)){
				$inputs = array_intersect_key($this->_requestData, array_flip($testFields) ); // we must flip the array because usually we will put directly the name as values, and intersect key expect key not values
			}

			$this->_inputs = $inputs;
		}
		return $this->_inputs;
	}

	/**
	 * Return the vo actually used by the saver
	 * @return mixed
	 */
	public function getVo(){
		if(is_null($this->_vo) && $this->_voName){
			$this->_vo = new $this->_voName();
		}
		return $this->_vo;
	}

	/**
	 * Return the mode of the saver from defined context param. Will also load edit and copy VO the first time called
	 * @return string
	 */
	public function getMode(){
		if(is_null($this->_mode)){
			$mode = 'add';
			if( ($id = (string) $this->getApplication()->getContext()->{$this->_urlParamId}) !== '' ){
				$mode = 'edit';
				if(!($this->_vo = $this->getDao()->getById($id))){
					throw new Exception('Try to edit a non existing vo '.$this->_voName.' : '.$id);
				}
			}elseif(  ($copyId = (string) $this->getApplication()->getContext()->{$this->_urlParamCopyId}) !== ''  ){
				$mode = 'copy';
				if(!($this->_vo = $this->getDao()->getById($copyId))){
					throw new Exception('Try to copy a non existing vo '.$this->_voName.' : '.$copyId);
				}
			}
			$this->_mode = $mode;
		}
		return $this->_mode;
	}

	/**
	 * Return the formMode string
	 * @return string
	 */
	public function getFormMode(){
		return $this->getMode().($this->hasSubmitter()?'ing':'');
	}

	/**
	 * Get the values to send to the form populateValue
	 * @return array
	 */
	public function getPopulateValues(){
		if($inputs = $this->getInputs()){
			return $inputs;
		}elseif($this->getMode() == 'edit' || $this->getMode() == 'copy'){
			return $this->getVo()->toArray();
		}elseif($defaultValues = $this->getDefaultValues()){
			return $defaultValues;
		}else{
			return $this->getVo()->toArray();
		}

		return array();
	}

	/**
	 * Return the store state of the saver. False didn't mean the saver execute and not work, see getError to see if something block
	 * @return boolean
	 */
	public function getStoreState(){
		return $this->_storeState;
	}

	/**
	 * Return the saver error, usually send by the validator
	 * getError will return nothing if you don't x() before
	 * @return array
	 */
	public function getError(){
		return $this->getValidator()->getError();
	}

	/**
	 * whether their is a submitter or not in the inputs
	 * @return boolean
	 */
	public function hasSubmitter(){
		if($inputs = $this->getInputs()){
			return array_key_exists($this->_formParamSubmitter, $inputs);
		}
		return false;
	}

	/**
	 * Auto set autoValues in the current vo
	 * @return null
	 */
	public function autoValues(){

		// siteId is a automatic one
		if(class_exists('Multisite')){
			$this->_autoValues['siteId'] = \Multisite::getInstance()->getSiteId() ;
		}

		if($this->_autoValues){
			foreach($this->_autoValues as $key => $value){
				if(property_exists($this->getVo(), $key) && $this->getVo()->getPrimaryKey()!==$key ){
					// test with Reflection if we can access the property
					$reflector = new \ReflectionClass($this->getVo());
					if($reflector->getProperty($key)->isPublic()){
						$this->getVo()->{$key} = $value;
					}
				}
			}
		}
	}

	public function initValidator(){
		$this->getValidator()
			->setMode( $this->getMode()=='edit' ? \Smally\Validator::MODE_EDIT : \Smally\Validator::MODE_NEW )
			->setActualVoId($this->getVo()->getId()?:null)
			;
		return $this;
	}

	public function initFilter(){
		$this->getFilter()
			->setMode( $this->getMode()=='edit' ? \Smally\Filter::MODE_EDIT : \Smally\Filter::MODE_NEW )
			->setActualVoId($this->getVo()->getId()?:null)
			;
		return $this;
	}

	public function initForm(){
		$this->getForm()
					->setNamePrefix($this->getFormPrefix()) // set the correct prefix
					->setValidator($this->getValidator())
					->populateValue($this->getPopulateValues())
					;
		return $this;
	}

	/**
	 * Saver core method
	 * This is for use in add,save,copy form context
	 * @return null
	 */
	public function x(){

		$this->initValidator();
		$this->initFilter();


		if($this->hasSubmitter()){

			$this->_inputs = $this->getFilter()->x($this->getInputs());

			if( $this->getValidator()->setTestValues($this->getInputs())->x(true, $this->_partEdit?array_keys($this->getInputs()):null) ) {

				$this->getVo()->initVars($this->getInputs());
				$this->autoValues(); // like siteId , authorId , etc ...

				if( $this->_storeState = $this->getVo()->store() ){

					if($redirect = $this->getRedirect()){
						$this->getApplication()->getRouter()->redirect($redirect);
					}
				}
			}else{
				$errors = $this->getError();
			}
		}

		$this->initForm();
		if(isset($errors)) $this->getForm()->populateError($errors);

		$this->sendToCallingController();

		return $this;
	}

	/**
	 * Auto push some values to callingController and its view
	 * @return null
	 */
	public function sendToCallingController(){
		if($callingController = $this->getController()){
			$callingController->getView()->form = $this->getForm() ;
			$callingController->getView()->formMode = $this->getFormMode() ;
			if($this->getMode()=='edit'){
				$callingController->getView()->item = $this->getVo() ;
			}
			$callingController->getView()->vo = new $this->_voName() ; // to have access to vo meta and others stuff
		}
	}

}