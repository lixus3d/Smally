<?php

namespace Smally\Controller;

class Crud extends \Smally\Controller {

	protected $_json = '';
	protected $_method = null;
	protected $_requestData = null;

	// protected $_error = array();

	public function __construct(\Smally\Application $application){
		parent::__construct($application);

		// Change view template to json
		$view = new \Smally\View($application);
		$view->setTemplatePath('json');
		$this->setView($view);
		// Change layout to json
		$this->getApplication()->setLayout('json');

		// Change headers of the response to handle json logic
		$this->getResponse()->setHeader('Content-type: application/json');
		$this->getResponse()->setHeader('Access-Control-Allow-Origin: *');

		// GET THE METHOD OF CRUD WANTED
		switch(strtolower($_SERVER['REQUEST_METHOD'])){
			case 'post':
				$this->_method = 'create';
			break;
			case 'get':
				$this->_method = 'read';
			break;
			case 'put':
				$this->_method = 'update';
			break;
			case 'delete':
				$this->_method = 'delete';
			break;
		}

		// RETRIEVE THE REQUEST CONTENT
		if($data = file_get_contents('php://input')){
			$this->_requestData = (array) @json_decode($data);
		}

	}

	/**
	 * Return the response in json automatically
	 * @return string Json
	 */
	public function __toString() {
		return $this->getJson();
	}

	/**
	 * Define the data to return , automatically encoded in json
	 * @param mixed $data The data you want to return
	 * @return \Smally\Controller\Crud
	 */
	public function setJson($data){
		$this->_json = json_encode($data);
		return $this;
	}

	/**
	 * Add errors to the response error
	 * @param mixed $error Array or string of error
	 * @return  \Smally\Controller\Rpc
	 */
	public function setError($error){
		if(is_array($error)){
			foreach($error as $errorStr){
				$this->setError($errorStr);
			}
		}else{
			if(!isset($this->_error)) $this->_error = array();
			$this->_error[] = $error;
		}
		return $this;
	}

	/**
	 * Execute the crud logic called
	 * @return mixed
	 */
	public function xCrud(){
		if( method_exists($this, $this->_method) ){
			return $this->{$this->_method}();
		}
		return null;
	}

	/**
	 * Subscribe to the onViewX event and place the json response in view->content
	 * @return null
	 */
	public function onViewX(){
		if(isset($this->_error)){
			$this->getResponse()->setHeader('HTTP/1.1 500 Internal Server Error');
			echo implode(', ', $this->_error);
			$this->getView()->content = null;
		}else{
			$this->getView()->content = $this->__toString();
		}
	}


	/**
	 * Return the response in json
	 * @return string Json
	 */
	public function getJson(){
		if(is_null($this->_json)){
			$this->_json = json_encode($this);
		}
		return $this->_json;
	}

}