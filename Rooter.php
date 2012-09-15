<?php

namespace Smally;

class Rooter {

	protected $_application = null;
	protected $_controller;

	protected $_baseUrl = null;
	protected $_uri = '';
	protected $_actualUrl = '';
	protected $_controllerPath = null;
	protected $_action = null;

	/**
	 * Construct the Rooter object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Rooter
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Define the base url for the rooter
	 * @param string $url
	 * @return \Smally\Rooter
	 */
	public function setBaseUrl($url){
		$this->_baseUrl = $url;
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
	 * Return the base url of the project
	 * @return string
	 */
	public function getBaseUrl(){
		if(!$this->_baseUrl){
			$this->parseUri();
		}
		return $this->_baseUrl;
	}

	/**
	 * Retturn the actual url called
	 * @return string
	 */
	public function getActualUrl(){
		return $this->_actualUrl;
	}

	/**
	 * Return the part of the url that is for the page / action
	 * @return string
	 */
	public function getRequestUri(){
		return $this->_uri;
	}

	/**
	 * Return the parsed controller path from the uri
	 * @return string A controller path to create the controller
	 */
	public function getControllerPath(){
		return $this->_controllerPath;
	}

	/**
	 * Return the controller object of the called page
	 * @return \Smally\Controller
	 */
	public function getController(){
		if(!isset($this->_controller)&&$this->_controllerPath){
			$this->_controller = $this->getControllerObject($this->_controllerPath)->setAction($this->getAction());
		}
		return $this->_controller;
	}

	/**
	 * Get a controller Object from a controller Path , common entry point for controller creation
	 * @param  string $controllerPath The controller path
	 * @return \Smally\Controller
	 */
	public function getControllerObject($controllerPath){
		$controllerName = '\controller\\'.$controllerPath;
		if(class_exists($controllerName)){
			$controller = new $controllerName($this->getApplication());
		}else throw new Exception('Invalid controller path given');
		return $controller;
	}

	/**
	 * Get the controller action called
	 * @return string
	 */
	public function getAction(){
		return $this->_action;
	}

	/**
	 * Cut the REQUEST_URI in two parts : "controller" part and "base" part
	 * @return \Smally\Rooter
	 */
	public function parseUri(){
		if(!$this->_baseUrl){
			$this->_baseUrl = 'http://'.$_SERVER['HTTP_HOST'].'/';

			// we get the request_uri and we compare with real_path to find similarity
			$queryArray = parse_url($_SERVER['REQUEST_URI']);

			$baseElems = array();
			$uriElems = array();

			if(isset($queryArray['path'])){
				$realPathArray = array_reverse(explode(DIRECTORY_SEPARATOR,REAL_PATH));
				$queryPathArray = array_reverse(explode('/',trim($queryArray['path'],'/')));

				$lastType = 'uri';
				$rpKey = 0;
				foreach($queryPathArray as $key => $rpElement){
					if(isset($realPathArray[$rpKey]) && $realPathArray[$rpKey] == $rpElement){
						$rpKey++;
						$baseElems[] = $rpElement;
						$lastType = 'base';
					}else{
						if($lastType == 'base'){
							$uriElems[] = array_pop($baseElems); // finaly the previous part was not from base
						}
						$uriElems[] = $rpElement;
						$lastType = 'uri';
					}
				}
			}

			if($baseElems){
				$this->_baseUrl .= implode('/',array_reverse($baseElems)).'/';
			}
			if($uriElems){
				$this->_uri = implode('/',array_reverse($uriElems));
			}
			$this->_actualUrl = $this->_baseUrl.$this->_uri;
		}
		return $this;
	}

	/**
	 * Redirect to any destination
	 * @param string $destination
	 * @param int $code
	 */
	public function redirect($destination='',$code=302){
		if(strpos($destination,'http') !== 0) $destination = $this->getBaseUrl() . $destination;
		header('Location: '.$destination,true,$code);
		die();
	}

	/**
	 * Convert an url action to a valid controller action inflected
	 * @param string $actionName
	 * @return string Inflected action
	 */
	public function parseAction($actionName){
		$actionName = preg_replace('#(-)([a-z])#e',"strtoupper('\\2')",$actionName);
		return $actionName;
	}

	/**
	 * Parse the request uri, find the base path, the controller and the action to do
	 * @throws Exception
	 * @return \Smally\Rooter
	 */
	public function x(){

		$urlSep = '/';
		$controllerPath = array();
		$action = '';

		$this->parseUri();

		if($queryPath = $this->getRequestUri()){

			// detect '..' and throw exception
			if(strpos($queryPath,'..') !== false) throw new Exception('We don\'t like ".." in the url ...');

			// remove trailing sep
			$queryPath = trim($queryPath,$urlSep);

			// Apply UrlRewriting Rules
			if($urlRewriting = $this->_application->getUrlRewriting()){
				if($destination = $urlRewriting->getRewrite($queryPath)){
					if(is_array($destination)){
						$queryPath = $destination['path'];
						if(isset($destination['matches'])){
							foreach($destination['matches'] as $key => $value){
								$this->getApplication()->getContext()->{$key} = $value;
							}
						}
					}else $queryPath = $destination;
				}
			}

			// explode the query in parts
			$parts = explode('/',$queryPath);
			foreach($parts as $part){
				$controllerPath[] = ucfirst($part);
			}
		}
		switch(count($controllerPath)){
			case 0;
				$controllerPath[] = 'Index';
			case 1:
				$controllerPath[] = 'index';
			break;
		}

		$this->_action = $this->parseAction(strtolower(array_pop($controllerPath)))?:'index';
		$this->_controllerPath = implode('\\',$controllerPath);

		return $this;
	}


}