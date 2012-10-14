<?php

namespace Smally\Controller;

class Rpc extends \Smally\Controller {


	public $code = 0;
	public $text = 'KO';

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
	 * Define the code in the response
	 * @param int $code An integer
	 * @param string $text Optional text for information
	 * @return  \Smally\Controller\Rpc
	 */
	public function setCode($code,$text=null){
		$this->code = $code;
		if($text){
			$this->setText($text);
		}
		return $this;
	}

	/**
	 * Define the text in the response
	 * @param string $text The text to put in the response
	 * @return  \Smally\Controller\Rpc
	 */
	public function setText($text='OK'){
		$this->text = $text;
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
			if(!isset($this->error)) $this->error = array();
			$this->error[] = $error;
		}
		return $this;
	}

	/**
	 * Return the response in json
	 * @return string Json
	 */
	public function getJson(){
		return json_encode($this);
	}

}