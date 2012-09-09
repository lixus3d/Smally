<?php

namespace Smally;

class Controller {

	protected $_application = null;
	protected $_action 		= null;
	protected $_view 		= null;

	/**
	 * Construct the global $context object
	 * @author Lixus3d <developpement@adreamaline.com>
	 * @param array $vars
	 */
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

	public function __call($name,$args){
		if(method_exists($this->getApplication(), $name)){
			return call_user_func_array(array($this->getApplication(),$name), $args);
			//return $this->getApplication()->$name($args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}

	public function setAction($action){
		$this->_action = $action;
		return $this;
	}

	public function getAction(){
		return $this->_action;
	}

	public function getView(){
		if(is_null($this->_view)){
			$this->_view = new View($this->getApplication());
			$this->_view->setTemplatePath( str_replace('Controller\\','',get_class($this)) . DIRECTORY_SEPARATOR . $this->getAction() );
		}
		return $this->_view;
	}

	public function x(){
		$method = $this->getAction().'Action';
		$this->$method();
		$this->getView()->x();
		return $this;
	}

}