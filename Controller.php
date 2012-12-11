<?php

namespace Smally;

abstract class Controller {

	protected $_application = null;
	protected $_action 		= null;
	protected $_view 		= null;

	/**
	 * Construct the controller object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Controller
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
	 * Wrapper of the Application instance so you can access every application function easily
	 * @param  string $name method called
	 * @param  array $args arguments
	 * @return mixed Application method return
	 */
	public function __call($name,$args){
		if(method_exists($this->getApplication(), $name)){
			return call_user_func_array(array($this->getApplication(),$name), $args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}

	/**
	 * Define the called action of the controller
	 * @param string $action The name of the action to call
	 * @return \Smally\Controller
	 */
	public function setAction($action){
		$this->_action = $action;
		return $this;
	}

	/**
	 * Define the view to use for this controller or action
	 * @param \Smally\View $view The forced view to use for this controller or action
	 * @return \Smally\Controller
	 */
	public function setView(\Smally\View $view){
		$this->_view = $view;
		return $this;
	}

	/**
	 * Define the view to use by passing only the view $templatePath
	 * @param string $templatePath the template path
	 * @return \Smally\Controller
	 */
	public function setViewTemplatePath($templatePath){
		$this->_view = new View($this->getApplication());
		$this->_view->setTemplatePath( $templatePath );
		return $this;
	}

	/**
	 * Return the called action of the controller
	 * @return string
	 */
	public function getAction(){
		return $this->_action;
	}

	/**
	 * Return the view object or create it the first time
	 * @return \Smally\View
	 */
	public function getView(){
		if(is_null($this->_view)){
			$this->_view = new View($this->getApplication());
			$this->_view
						->setController($this)
						->setTemplatePath( str_replace('Controller\\','',get_class($this)) . DIRECTORY_SEPARATOR . $this->getAction() )
						;
		}
		return $this->_view;
	}

	/**
	 * Execute the controller called action and the attached view
	 * @return \Smally\Controller
	 */
	public function x($params=array()){

		// pseudo event system
		if(method_exists($this, 'onX')){
			$this->onX();
		}
		$method = $this->getAction().'Action';
		$this->$method($params);

		// pseudo event system before view execution
		if(method_exists($this, 'onViewX')){
			$this->onViewX();
		}
		$this->getView()->x($params);

		return $this;
	}

}