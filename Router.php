<?php

namespace Smally;

class Router {

	protected $_application = null;
	protected $_controller;

	protected $_defaultController 	= null;
	protected $_defaultAction 		= null;

	protected $_baseUrl = null;
	protected $_uri = '';
	protected $_uriQuery = '';
	protected $_actualUrl = '';
	protected $_controllerPath = null;
	protected $_action = null;

	protected $_clonePrefix = '';

	protected $_logLevel = null;
	protected $_logger = null;

	/**
	 * Construct the Router object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
		if($logger = $application->getLogger()){
			$this->_logger = $logger;
			$this->_logLevel = $logger->getLogLevel('Router');
		}
		$this->setDefaultController((string)$this->getApplication()->getConfig()->project->default->router->controller?:'Index');
		$this->setDefaultAction((string)$this->getApplication()->getConfig()->project->default->router->action?:'index');
		$this->setClonePrefix((string)$this->getApplication()->getConfig()->smally->multisite->clonePrefix);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\Router
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Define the base url for the Router
	 * @param string $url
	 * @return \Smally\Router
	 */
	public function setBaseUrl($url){
		$this->_baseUrl = $url;
		return $this;
	}

	/**
	 * Define the clone prefix to use to prefix the uri
	 * @param string $clonePrefix The string to use to prefix uri part
	 * @return \Smally\Router
	 */
	public function setClonePrefix($clonePrefix){
		$this->_clonePrefix = $clonePrefix;
		return $this;
	}

	/**
	 * Define the default controller to use when not present in uri
	 * @param string $defaultController The default controller name
	 * @return \Smally\Router
	 */
	public function setDefaultController($defaultController){
		$this->_defaultController = $defaultController;
		return $this;
	}

	/**
	 * Define the default action to use when not present in uri
	 * @param string $defaultAction The default action name
	 * @return \Smally\Router
	 */
	public function setDefaultAction($defaultAction){
		$this->_defaultAction = $defaultAction;
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
	public function getRequestUri($full=true){
		return $this->_uri.($full&&$this->_uriQuery?'?'.$this->_uriQuery:'');
	}

	/**
	 * Return the parsed controller path from the uri
	 * @return string A controller path to create the controller
	 */
	public function getControllerPath(){
		return $this->_controllerPath;
	}

	/**
	 * Return the full action path prefixed by the controller path
	 * @return string
	 */
	public function getActionPath(){
		return $this->_controllerPath .'\\'.$this->getAction();
	}

	/**
	 * Return the controller object of the called page
	 * @return \Smally\Controller
	 */
	public function getController(){
		if(!isset($this->_controller)&&$this->_controllerPath){
			$this->_controller = $this->getControllerObject($this->_controllerPath)
											->setAction($this->getAction())
											;
		}
		return $this->_controller;
	}

	/**
	 * Get a controller Object from a controller Path , common entry point for controller creation
	 * @param  string $controllerPath The controller path
	 * @return \Smally\Controller
	 */
	public function getControllerObject($controllerPath){
		$splitPosition = strrpos($controllerPath,'\\');
		$controllerName = substr($controllerPath, 0, $splitPosition).'\\Controller'.substr($controllerPath, $splitPosition);
		if(class_exists($controllerName)){
			$controller = new $controllerName($this->getApplication());
		}else throw new Exception('Invalid controller path given : '.$controllerName);
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
	 * Wrapper to the Logger but test if we have to log before sending
	 * @param  string $text     Usually the root to log
	 * @param  int $level       The level of the log
	 * @param  int $destination Destination of the log
	 * @return null
	 */
	public function log($text,$level=\Smally\Logger::LVL_INFO,$destination=\Smally\Logger::DEST_LOG){
		if(!is_null($this->_logger)&&$this->_logLevel<=$level){
			$this->_logger->log($text,$level,$destination);
		}
	}

	/**
	 * Cut the REQUEST_URI in two parts : "controller" part and "base" part
	 * @return \Smally\Router
	 */
	public function parseUri(){
		if(!$this->_baseUrl){
			$this->_baseUrl = 'http://'.$_SERVER['HTTP_HOST'].'/';

			// we get the request_uri and we compare with real_path to find similarity
			$queryArray = parse_url($_SERVER['REQUEST_URI']);

			$baseElems = array();
			$uriElems = array();

			if(isset($queryArray['path'])){
				$realPathArray = array_reverse(explode(DIRECTORY_SEPARATOR,trim(REAL_PATH,'/\\')));
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
							$rpKey--;
						}
						$uriElems[] = $rpElement;
						$lastType = 'uri';
					}
				}
			}

			if($baseElems){
				$this->_baseUrl .= implode('/',array_reverse($baseElems)).'/'. $this->_clonePrefix ;
			}
			if($uriElems){
				$this->_uri = implode('/',array_reverse($uriElems));
			}

			if(isset($queryArray['query'])){
				$this->_uriQuery = $queryArray['query'];
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
		if(strpos($destination,'http') !== 0) $destination = $this->getBaseUrl() . $this->getApplication()->makeControllerUrl($destination);
		header('Location: '.$destination,true,$code);
		die();
	}

	/**
	 * Convert an url action to a valid controller action inflected
	 * @param string $actionName
	 * @return string Inflected action
	 */
	public function parseAction($actionName){
		$actionName = preg_replace_callback('#-([a-z])#',function($matches){return strtoupper($matches[1]);},$actionName);
		return $actionName;
	}

	/**
	 * Parse the request uri, find the base path, the controller and the action to do
	 * @throws Exception
	 * @return \Smally\Router
	 */
	public function x(){

		$controllerPath = array();

		$this->parseUri();

		if($queryPath = $this->getRequestUri(false)){

			// detect '..' and throw exception
			if(strpos($queryPath,'..') !== false) throw new Exception('We don\'t like ".." in the url ...');

			// remove trailing sep
			$queryPath = trim($queryPath,'/');
			$queryPathControllerStyle =  str_replace('/','\\',$queryPath); // Right the controller way with \ like namespace

			// Apply UrlRewriting Rules
			if($urlRewriting = $this->_application->getUrlRewriting()){ // Do we have a valid Url Rewriting element

				// If we found an old url redirect, then we redirect to this new url to avoid SEO problem
				if( $redirectRule = $urlRewriting->hasRedirectRule($queryPath) ){
					$this->redirect($redirectRule['url'],$redirectRule['httpStatus']);
				}

				// If we found a url rewriting for the controller path given in $queryPath, then we redirect to this specific url to avoid SEO duplicate content
				if( $urlRewriting->hasControllerRewriting( $queryPathControllerStyle ) ){
					$url = $urlRewriting->getControllerRewriting( $queryPathControllerStyle, $this->getApplication()->getContext()->toArray() );
					$this->redirect($url,301);
				}

				if($destination = $urlRewriting->getRewrite($queryPath)){
					if(is_array($destination)){
						$queryPathControllerStyle = $destination['path'];
						if(isset($destination['matches'])){
							foreach($destination['matches'] as $key => $value){
								$this->getApplication()->getContext()->{$key} = $value;
							}
						}
					}else $queryPathControllerStyle = $destination;
				}
			}

			// explode the query in parts
			$parts = explode('\\',$queryPathControllerStyle);
			foreach($parts as $part){
				$controllerPath[] = ucfirst($part);
			}
		}

		switch(count($controllerPath)){
			case 0;
				$controllerPath[] = $this->_defaultAction; // index
			case 1:
				array_unshift($controllerPath, $this->_defaultController); // Index
			break;
		}

		// Fix first letter of action to lowercase
		$this->_action = lcfirst($this->parseAction(array_pop($controllerPath)));
		$this->_controllerPath = implode('\\',$controllerPath);

		$this->log($this->getActionPath());

		return $this;
	}


}