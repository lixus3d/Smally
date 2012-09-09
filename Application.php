<?php

namespace Smally;

class Application {

	static protected $_singleton 	= null;

	protected $_config 				= null;
	protected $_response 			= null;
	protected $_rooter 				= null;
	protected $_context 			= null;
	protected $_meta				= null;
	protected $_urlRewriting		= null;

	protected $_view				= null;
	protected $_layout				= 'global';

	protected $_css					= array();
	protected $_js					= array();

	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
	}

	/**
	 * Set the singleton instance of Application
	 * @return \Smally\Application
	 */
	public function setInstance(){
		return self::$_singleton = $this;
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
	 * Get the config object or create a new empty one for compatibility
	 * @return \Smally\Config
	 */
	public function getConfig(){
		if(is_null($this->_config)) $this->_config = new Config();
		return $this->_config;
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
	 * Return the base Url of the project
	 * @param  string $path Suffix the base url with this $path
	 * @return string
	 */
	public function getBaseUrl($path=''){
		return $this->getRooter()->getBaseUrl().$path;
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