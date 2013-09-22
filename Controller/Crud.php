<?php

namespace Smally\Controller;

class Crud extends \Smally\Controller {

	protected $_json = '';
	protected $_method = null;

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
		$this->getView()->content = $this->__toString();
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