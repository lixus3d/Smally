<?php

namespace Smally;

class Application {

	const ENV_DEVELOPMENT 			= 'development';
	const ENV_PRODUDCTION			= 'production';
	const ENV_STAGING				= 'staging';

	static protected $_singleton 	= null;

	protected $_environnement 		= self::ENV_DEVELOPMENT;
	protected $_logger				= null;

	protected $_bootstrap			= null;
	protected $_factory				= null;

	protected $_config 				= null;
	protected $_context 			= null;
	protected $_rooter 				= null;
	protected $_view				= null;
	protected $_response 			= null;

	protected $_meta				= null;
	protected $_urlRewriting		= null;
	protected $_navigation			= null;

	protected $_layout				= 'global';

	protected $_css					= array();
	protected $_js					= array();

	private $__startTime 			= 0;

	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
		$this->__startTime = microtime(true);
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
	 * Return the application logger
	 * @return \Smally\Logger
	 */
	public function getLogger(){
		if(is_null($this->_logger)){
			$this->_logger = new Logger($this->getConfig()->smally->logger->path!=''?:ROOT_PATH.'logs/',$this);
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
	 * Get the rooter object or create it the first time
	 * @return \Smally\Rooter
	 */
	public function getRooter(){
		if(is_null($this->_rooter)) $this->_rooter = new Rooter($this);
		return $this->_rooter;
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
			$this->_view->setTemplatePath($this->_layout);
		}
		return $this->_view;
	}

	/**
	 * Get the meta object or create it the first time
	 * @return \Smally\Meta
	 */
	public function getMeta(){
		if(is_null($this->_meta)) $this->_meta = new Meta($this,$_REQUEST);
		return $this->_meta;
	}

	/**
	 * Get a Bootstrap class if one exist in the project, return null otherwise
	 * @return \Bootstrap
	 */
	public function getBootstrap(){
		if(is_null($this->_bootstrap)&&class_exists('\Bootstrap')){
			$this->_bootstrap = new \Bootstrap($this);
		}
		return $this->_bootstrap;
	}

	/**
	 * Get the context object or create it the first time with $_REQUEST
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
	public function getBaseUrl($path='',$type='www',$htmlspecialchars=true){
		static $baseUrl = null;
		if(is_null($baseUrl)){
			$baseUrl = $this->getRooter()->getBaseUrl();
		}

		switch($type){
			case 'www': break;
			case $this->isDev(): // If we are in developpement context, then we always use the standard base url but we prefix with type directory
				$path = $type.'/'.$path;
			break;
			case 'assets':
				if( !$this->getConfig()->smally->paths->assets->isEmpty() && $assetsPaths = $this->getConfig()->smally->paths->assets->toArray()){
					// generate a unique key (ip + $path first char)
					$ip = substr(strrchr($this->getContext()->getIp(), '.'),1); // serv always same domain to a particular ip
					$ip += ord(basename($path)) + strlen($path);
					$url = $assetsPaths[$ip % count($assetsPaths)];
				}
			break;
		}
		if(!isset($url)) $url = $baseUrl;
		$url .= $path;
		return $htmlspecialchars?htmlspecialchars($url):$url;
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

	/**
	 * Return the name of the global layout
	 * @return string
	 */
	public function getLayout(){
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
		return str_replace('\\','/',$controllerPath);
	}

	/**
	 * Execute the application logic
	 * @return \Smally\Application
	 */
	public function x(){
		// Execute the bootstrap if present
		if($this->getBootstrap()) $this->getBootstrap()->x();

		// Execute the rooter logic : Parse URL, define controller path and controller action
		$this->getRooter()->x();

		// Execute the controller and view
		$this->getRooter()->getController()->x();

		// Place view content in the view->content property of the layout
		$this->getView()->content = $this->getRooter()->getController()->getView()->getContent();

		// Execute the application view (layout)
		$this->getView()->x();

		// Execute response logic
		$this->getResponse()->setContent($this->getView()->getContent())->x();

		return $this;
	}

}