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

	protected $_css					= array();
	protected $_js					= array();

	public function __construct(){
		if(!self::$_singleton instanceof self){
			$this->setInstance();
		}
	}

	public function setInstance(){
		return self::$_singleton = $this;
	}

	public function setConfig( \Smally\Config $config){
		$this->_config = $config;
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
	 * @return \EG\Controller\Front
	 */
	public function setCss($css){
		$this->_css[$css] = $css;
		return $this;
	}

	/**
	 * Return the singleton
	 * @return \Smally
	 */
	static public function getInstance(){
		if(!self::$_singleton instanceof self){
			new self();
		}
		return self::$_singleton;
	}

	public function getConfig(){
		if(is_null($this->_config)) $this->_config = new Config();
		return $this->_config;
	}

	public function getResponse(){
		if(is_null($this->_response)) $this->_response = new Response($this);
		return $this->_response;
	}

	public function getRooter(){
		if(is_null($this->_rooter)) $this->_rooter = new Rooter($this);
		return $this->_rooter;
	}

	public function getContext(){
		if(is_null($this->_context)) $this->_context = new Context($this,$_REQUEST);
		return $this->_context;
	}

	public function getUrlRewriting(){
		if(class_exists('UrlRewriting')){
			$this->_urlRewriting = new \UrlRewriting($this);
		}
		return $this->_urlRewriting;
	}

	public function getView(){
		if(is_null($this->_view)){
			$this->_view = new View($this);
			$this->_view->setTemplatePath('global');
		}
		return $this->_view;
	}

	public function getMeta(){
		if(is_null($this->_meta)) $this->_meta = new Meta($this,$_REQUEST);
		return $this->_meta;
	}

	public function getBootstrap(){
		if(class_exists('\Bootstrap')){
			$this->_bootstrap = new \Bootstrap($this);
		}
		return $this->_bootstrap;
	}

	public function getBaseUrl($path=''){
		return $this->getRooter()->getBaseUrl().$path;
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

	public function x(){
		$this->getBootstrap()->x();

		$this->getRooter()->x(); // Parse URL , define controller path

		$this->getRooter()->getController()->x(); // Execute the controller and view of the content

		$this->getView()->content = $this->getRooter()->getController()->getView()->getContent(); // Place view content in the view->content property of the layout
		$this->getView()->x(); // Execute the view (layout)

		$this->getResponse()->setContent($this->getView()->getContent())->x(); // Launch response
	}

}