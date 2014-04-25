<?php

namespace Smally;

class Application {

	const ENV_DEVELOPMENT 			= 'development';
	const ENV_PRODUCTION			= 'production';
	const ENV_STAGING				= 'staging';

	static protected $_singleton 	= null;

	protected $_language 			= 'fr';

	protected $_environnement 		= self::ENV_DEVELOPMENT;
	protected $_logger				= null;

	protected $_siteNamespace 		= null;

	protected $_init				= null;
	protected $_bootstrap			= null;
	protected $_factory				= null;

	protected $_config 				= null;
	protected $_context 			= null;
	protected $_Router 				= null;
	protected $_view				= null;
	protected $_response 			= null;
	protected $_translate 			= null;

	protected $_meta				= null;
	protected $_urlRewriting		= null;
	protected $_navigation			= null;

	protected $_layout				= null;

	protected $_css					= array();
	protected $_js					= array();

	private $__startTime 			= 0;

	public function __construct(){
		$this->__startTime = microtime(true);
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
		defined('SMALLY_PLATFORM') || define('SMALLY_PLATFORM','windows');
	}

	/**
	 * You can't serialize an application
	 * @return array
	 */
	public function __sleep(){
		return array();
	}

	/**
	 * Set the singleton instance of Application
	 * @return \Smally\Application
	 */
	public function setInstance(){
		return self::$_singleton = $this;
	}

	/**
	 * Define the used environnement for the current execution
	 * @param string $environnement The environnement you want for the current Application (use Application constant)
	 * @return \Smally\Application
	 */
	public function setEnvironnement($environnement){
		$this->_environnement = $environnement;
		return $this;
	}

	/**
	 * Define the language to use for text and errors
	 * @param string $language A language in 2 char format
	 * @return \Smally\Application
	 */
	public function setLanguage($language){
		$this->_language = $language;
		return $this;
	}

	/**
	 * Define the application config object
	 * @param \Smally\Config $config A valid config object
	 * @return \Smally\Application
	 */
	public function setConfig( \Smally\Config $config){
		$this->_config = $config;
		return $this;
	}

	/**
	 * Define the application navigation object
	 * @param \Smally\Navigation $config A valid navigation object
	 * @return \Smally\Application
	 */
	public function setNavigation( \Smally\Navigation $navigation){
		$this->_navigation = $navigation;
		$this->_navigation->setApplication($this);
		return $this;
	}

	/**
	 * Define the application bootstrap object
	 * @param \Smally\AbstractBootstrap $bootstrap A valid abstract bootstrap object
	 * @return  \Smally\Application
	 */
	public function setBootstrap( \Smally\AbstractBootstrap $bootstrap){
		$this->_bootstrap = $bootstrap;
		return $this;
	}

	/**
	 * Define the application urlRewriting object
	 * @param \Smally\AbstractUrlRewriting $urlRewriting A valid abstract url rewriting object
	 * @return  \Smally\Application
	 */
	public function setUrlRewriting( \Smally\AbstractUrlRewriting $urlRewriting){
		$this->_urlRewriting = $urlRewriting;
		return $this;
	}

	/**
	 * Define the global layout of the page
	 * @param string $layout A valid php layout
	 * @return \Smally\Application
	 */
	public function setLayout($layout){
		$this->_layout = $layout;
		return $this;
	}

	/**
	 * Add a js to the current page
	 * @param string $script
	 * @return \Smally\Application
	 */
	public function setJs($script){
		$this->_js[$script] = $script;
		return $this;
	}

	/**
	 * Add a css to the current page
	 * @param string $css
	 * @return \Smally\Application
	 */
	public function setCss($css){
		$this->_css[$css] = $css;
		return $this;
	}

	/**
	 * Force a multisite id in the current execution
	 * @param int $siteId The site id you want to force
	 * @return \Multisite
	 */
	public function setMultisiteId($siteId){
		return \Multisite::getInstance()->x($siteId);
	}

	/**
	 * Define a site specific namespace that will load specific bootstrap and init model
	 * @param string $siteNamespace A namespace
	 * @return \Smally\Application
	 */
	public function setSiteNamespace($siteNamespace){

		$this->_siteNamespace = $siteNamespace;

		// Specific Init of the site
		$className = '\\'.$this->_siteNamespace.'\\Init';
		if(class_exists($className)){
			$this->_init = new $className($this);
			$this->_init->x();
		}
		// Specific Bootsrap
		$className = '\\'.$this->_siteNamespace.'\\Bootstrap';
		if(class_exists($className)){
			$this->_bootstrap = new $className($this);
		}
		// Specific UrlRewriting
		$className = '\\'.$this->_siteNamespace.'\\UrlRewriting';
		if(class_exists($className)){
			$this->_urlRewriting = new $className();
		}

		return $this;
	}

	/**
	 * Are we in a developpement context ?
	 * @return boolean
	 */
	public function isDev(){
		return $this->_environnement == self::ENV_DEVELOPMENT;
	}

	/**
	 * Return the singleton
	 * @return \Smally\Application
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof self){
			new self();
		}
		return self::$_singleton;
	}

	/**
	 * Return the execution time between now and start of application instanciation
	 * @return float
	 */
	public function getExecutionTime(){
		return microtime(true)-$this->__startTime;
	}

	/**
	 * Return the defined environnement , to test if you are in developpement context use isDev() method
	 * @return string
	 */
	public function getEnvironnement(){
		return $this->_environnement;
	}

	/**
	 * Return the defined language
	 * @return string
	 */
	public function getLanguage(){
		return $this->_language;
	}

	/**
	 * Return the application logger
	 * @return \Smally\Logger
	 */
	public function getLogger(){
		if(is_null($this->_logger)){
			$this->_logger = new Logger($this->getConfig()->smally->logger->path!=''?:LOG_PATH,$this);
			$this->_logger->setInstance(); // The application logger is a singleton and by so the default logger
		}
		return $this->_logger;
	}

	/**
	 * Get the config object or create a new empty one for compatibility
	 * @return \Smally\Config
	 */
	public function getConfig(){
		if(is_null($this->_config)) $this->_config = new Config();
		return $this->_config;
	}

	/**
	 * Get the translate object or create a new empty one for compatibility
	 * @return \Smally\Translate
	 */
	public function getTranslate(){
		if(is_null($this->_translate)) $this->_translate = new Translate($this);
		return $this->_translate;
	}

	/**
	 * Return the factory of the application, you can have your own factory or use the Smally one
	 * @return \Smally\Factory
	 */
	public function getFactory(){
		if(is_null($this->_factory)){
			if(class_exists('\Factory')){
				$this->_factory = new \Factory($this);
			}else{
				$this->_factory = new Factory($this);
			}
		}
		return $this->_factory;
	}

	/**
	 * Get the Response object or create it the first time
	 * @return \Smally\Response
	 */
	public function getResponse(){
		if(is_null($this->_response)) $this->_response = new Response($this);
		return $this->_response;
	}

	/**
	 * Get the Router object or create it the first time
	 * @return \Smally\Router
	 */
	public function getRouter(){
		if(is_null($this->_Router)) $this->_Router = new Router($this);
		return $this->_Router;
	}

	/**
	 * Get the context object or create it the first time with $_REQUEST
	 * @return \Smally\Context
	 */
	public function getContext(){
		if(is_null($this->_context)) $this->_context = new Context($this,$_REQUEST);
		return $this->_context;
	}

	/**
	 * Get a UrlRewriting class if one exist in the project , return null otherwise
	 * @return \UrlRewriting
	 */
	public function getUrlRewriting(){
		if(is_null($this->_urlRewriting)&&class_exists('UrlRewriting')){
			$this->_urlRewriting = new \UrlRewriting($this);
		}
		return $this->_urlRewriting;
	}

	/**
	 * Get the layout view object or create it the first time
	 * @return \Smally\View
	 */
	public function getView(){
		if(is_null($this->_view)){
			$this->_view = new View($this);
			$this->_view->setTemplatePath($this->getLayout());
		}
		return $this->_view;
	}

	/**
	 * Get the meta object or create it the first time
	 * @return \Smally\Meta
	 */
	public function getMeta(){
		if(is_null($this->_meta)) $this->_meta = new Meta($this);
		return $this->_meta;
	}

	/**
	 * Get a Init class if one exist in the project, return null otherwise
	 * @return \Init
	 */
	public function getInit(){
		if(is_null($this->_init)&&class_exists('Init')){
			$this->_init = new \Init($this);
		}
		return $this->_init;
	}

	/**
	 * Get a Bootstrap class if one exist in the project, return null otherwise
	 * @return \Bootstrap
	 */
	public function getBootstrap(){
		if(is_null($this->_bootstrap)&&class_exists('Bootstrap')){
			$this->_bootstrap = new \Bootstrap($this);
		}
		return $this->_bootstrap;
	}

	/**
	 * Get the current navigation instance object or create an empty one if not yet defined
	 * @return \Smally\Context
	 */
	public function getNavigation(){
		if(is_null($this->_navigation)) $this->_navigation = new Navigation();
		return $this->_navigation;
	}

	/**
	 * Return a new Acl object
	 * @return \Smally\Acl
	 */
	public function getAcl(){
		return new Acl();
	}

	/**
	 * Return the base Url of the project
	 * @param  string $path Suffix the base url with this $path
	 * @param  string  $type             Default type to www
	 * @param  boolean $htmlspecialchars Does we convert the string to be href compliant*
	 * @return string
	 */
	public function getBaseUrl($path='',$type='www',$htmlspecialchars=true,$forceComplete=false){
		static $baseUrl = null;

		if(is_null($baseUrl)){
			if($this->isDev()&&!$forceComplete){
				$baseUrl = $this->getRouter()->getBaseUrl(true,true);
			}else{
				$baseUrl = $this->getRouter()->getBaseUrl();
			}
		}

		switch($type){
			case 'www': break;
			case $this->isDev(): // If we are in developpement context, then we always use the standard base url but we prefix with type directory
				if( isset($this->getConfig()->smally->paths->{$type.'Prefix'}) ) $pathPrefix = $this->getConfig()->smally->paths->{$type.'Prefix'};
				else $pathPrefix = '';
				$path = $pathPrefix.$type.'/'.$path;
			break;
			case 'data':
				if( !$this->getConfig()->smally->paths->data->isEmpty() && $dataPaths = $this->getConfig()->smally->paths->data->toArray()){
					// generate a unique key (ip + $path first char)
					$ip = substr(strrchr($this->getContext()->getIp(), '.'),1); // serv always same domain to a particular ip
					$ip += ord(basename($path)) + strlen($path);
					$url = $dataPaths[$ip % count($dataPaths)];
				}else{
					$path = $type.'/'.$path;
				}
			break;
			case 'assets':
				if( !$this->getConfig()->smally->paths->assets->isEmpty() && $assetsPaths = $this->getConfig()->smally->paths->assets->toArray()){
					// generate a unique key (ip + $path first char)
					$ip = substr(strrchr($this->getContext()->getIp(), '.'),1); // serv always same domain to a particular ip
					$ip += ord(basename($path)) + strlen($path);
					$url = $assetsPaths[$ip % count($assetsPaths)];
				}else{
					$path = $type.'/'.$path;
				}
			break;
		}
		if(!isset($url)) $url = $baseUrl;
		$url .= $path;
		return $htmlspecialchars?htmlspecialchars($url,ENT_COMPAT,'UTF-8'):$url;
	}

	/**
	 * Shortcut to make assets url
	 * @param  string  $path             Suffix to the base url of assets file
	 * @param  string  $type             Default type to assets
	 * @param  boolean $htmlspecialchars Does we convert the string to be href compliant
	 * @return string
	 */
	public function urlAssets($path='',$type='assets',$htmlspecialchars=true){
		return $this->getBaseUrl($path,$type,$htmlspecialchars);
	}

	public function urlData($path='',$type='data',$htmlspecialchars=true){
		return $this->getBaseUrl($path,$type,$htmlspecialchars);
	}


	/**
	 * Return the name of the global layout
	 * @return string
	 */
	public function getLayout(){
		if(is_null($this->_layout)){
			$this->_layout = (string)$this->getConfig()->project->default->template->global?:'global';
		}
		return $this->_layout;
	}

	/**
	 * Retrun all page JS
	 * @return array
	 */
	public function getJs(){
		return $this->_js;
	}

	/**
	 * Return all page CSS
	 * @return array
	 */
	public function getCss(){
		return $this->_css;
	}

	/**
	 * Return the constructed url of a controller
	 * @param  string $controllerPath The controller path
	 * @param  array  $params         Url parameters to add
	 * @return string
	 */
	public function makeControllerUrl($controllerPath,$params=array()){
		if($urlRewriting=$this->getUrlRewriting()){
			return $urlRewriting->getControllerRewriting($controllerPath,$params);
		}
		return str_replace('\\','/',$controllerPath); // The url path are with / not \
	}

	/**
	 * Return a full controller url with base url
	 * @param  string $controllerPath The controller path
	 * @param  array  $params         Url parameters to add
	 * @param  boolean $htmlspecialchars Does we convert the string to be href compliant
	 * @return string
	 */
	public function getControllerUrl($controllerPath,$params=array(),$htmlspecialchars=true){
		return $this->getBaseUrl($this->makeControllerUrl($controllerPath,$params),'www',$htmlspecialchars);
	}

	/**
	 * Execute the application logic
	 * @return \Smally\Application
	 */
	public function x(){

		if($this->getInit()) $this->getInit()->x();

		// Execute the Router logic and get the controller: Parse URL, define controller path and controller action
		$controller = $this->getRouter()->x()->getController();

		// Execute the bootstrap if present
		if($bootstrap = $this->getBootstrap()) $bootstrap->x();

		// Execute the controller and view
		$controller->x();

		$render = $controller->getView()->getRender();

		\Smally\Messager::getInstance()->x();

		// Place the view content in the global layout view and execute layout view
		$layoutView = $this->getView()->setContent($render)->x();


		// Execute response logic
		$this->getResponse()->setContent($layoutView->getRender())->x();

		return $this;
	}

}
