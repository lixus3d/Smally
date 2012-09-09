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


	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	public function setBaseUrl($url){
		$this->_baseUrl = $url;
	}

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

	public function getControllerPath(){
		return $this->_controllerPath;
	}

	public function getController(){
		if(!isset($this->_controller)&&$this->_controllerPath){
			$this->_controller = $this->getControllerObject($this->_controllerPath)->setAction($this->getAction());
		}
		return $this->_controller;
	}

	public function getControllerObject($controllerPath){
		$controllerName = '\Controller\\'.$controllerPath;
		$controller = new $controllerName($this->getApplication());
		return $controller;
	}

	public function getAction(){
		return $this->_action;
	}

	/**
	 * Cut the REQUEST_URI in two parts : "controller" part and "base" part
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
			if(strpos($queryPath,'..') !== false) throw new Exception('Somebody is trying to traverse by url !');

			// remove trailing sep
			$queryPath = trim($queryPath,$urlSep);

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

		$this->_action = $this->parseAction(array_pop($controllerPath))?:'index';
		$this->_controllerPath = implode('\\',$controllerPath);

		return $this;
	}

	/**
	 * Convert an url action to a valid controller action
	 * @param unknown_type $actionName
	 */
	public function parseAction($actionName){
		$actionName = preg_replace('#(-)([a-z])#e',"strtoupper('\\2')",$actionName);
		return $actionName;
	}

}