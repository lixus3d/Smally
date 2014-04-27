<?php

namespace Smally;

class View {

	protected $_application = null;
	protected $_controller = null;

	protected $_templatePath = null;

	protected $_parentView = null;

	public $content = ''; // eventually a sub content in the view. Usually for a Global layout
	protected $_render = '';

	/**
	 * Construct the View object
	 * @param \Smally\Application $application reverse reference to the application
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	/**
	 * Set the application reverse reference
	 * @param \Smally\Application $application Current application linked to this object
	 * @return \Smally\View
	 */
	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	/**
	 * Set the controller reverse reference
	 * @param \Smally\Controller $controller The controller linked to the view
	 * @return \Smally\View
	 */
	public function setController(\Smally\Controller $controller){
		$this->_controller = $controller;
		return $this;
	}

	/**
	 * Define the view template path
	 * @param string $templatePath the template path
	 * @return \Smally\View
	 */
	public function setTemplatePath($templatePath){
		$this->_templatePath = $templatePath;
		return $this;
	}

	public function setParentView(\Smally\View $parentView){
		$this->_parentView = $parentView;
		return $this;
	}

	/**
	 * Import an array of key value to public properties of the view
	 * @param  array $array Array of key => value, where key become object property name
	 * @return \Smally\View
	 */
	public function import($array){
		if(is_array($array)){
			foreach($array as $key => $value){
				if(strpos($key,'_')===0)continue;
				$this->{$key} = $value;
			}
		}
		return $this;
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
	 * Define the view content/render
	 * @param string $content The content of the view
	 */
	public function setContent($content){
		$this->content = $content;
		return $this;
	}

	/**
	 * Define the view content/render
	 * @param string $content The content of the view
	 */
	public function setRender($render){
		$this->_render = $render;
		return $this;
	}

	/**
	 * Compatibility to avoid notice when a property is not defined
	 * @param  string $name
	 * @return null
	 */
	public function __get($name){
		return null;
	}

	/**
	 * Return the application reverse referenced
	 * @return \Smally\Application
	 */
	public function getApplication(){
		return $this->_application;
	}

	/**
	 * Return the controller linked to the view
	 * @return \Smally\Controller
	 */
	public function getController(){
		return $this->_controller;
	}

	/**
	 * Return the template path of this view
	 * @return string
	 */
	public function getTemplatePath(){
		return $this->_templatePath;
	}

	public function getSubView($subViewTemplatePath){
		$subViewTemplatePath = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$subViewTemplatePath);
		$subView = clone($this);
		$subView->setParentView($this);
		$subView->setTemplatePath($subViewTemplatePath);
		return $subView;
	}

	/**
	 * Get the sub content of the view, default empty
	 * @return string
	 */
	public function getContent(){
		return $this->content;
	}

	/**
	 * Get the render of the view, default empty
	 * @return string
	 */
	public function getRender(){
		return $this->_render;
	}

	/**
	 * Return the content of a meta type
	 * @param string $type
	 * @return mixed
	 */
	public function getMetaStandard($type="title"){
		if($metas = $this->getApplication()->getMeta()){
			return $metas->getType($type);
		}
		return null;
	}

	/**
	 * Return the html tags of the additional meta tag
	 * @return string
	 */
	public function getMetaAdditional(){
		$output = array();
		$meta = $this->getApplication()->getMeta();
		if($metas = $meta->getOtherMetas()){
			foreach($metas as $element){
				$output[] = '<meta'.\Smally\Util::toAttributes($element).'/>';
			}
		}
		return implode(NN.TT,$output);
	}

	/**
	 * Return the html tags of the css files
	 * @return string
	 */
	public function getCss(){

		$output = array();

		foreach($this->getApplication()->getCss() as $file){
			if(strpos($file,'http')!==0){
				if($mtime = \Smally\Assets::getInstance()->getAssetMtime($file) ){
					$file = substr($file,0,strrpos($file, '.')) . '.' . $mtime . strrchr($file, '.');
				}
				if(strpos($file,'.less') > 0 && !$this->getApplication()->isDev()) {
					$file .= '.css';
				}
				$url = $this->urlAssets($file);
			}else{
				$url = $file;
			}

			$output[] = '<link rel="stylesheet"  type="text/css"  media="all" href="'.$url.'"/>';
		}
		return implode(NN.TT,$output);
	}

	/**
	 * Return the html tags of the js files
	 * @return string
	 */
	public function getJs(){

		$addMinify = false;
		$output = array();
		$preoutput = array();

		foreach($this->getApplication()->getJs() as $file){
			if(\Smally\Assets::getInstance()->isMinify($file)){
				$addMinify = true;
				if(!$this->getApplication()->isDev()) {
					continue;
				}
			}
			$url = strpos($file,'http')===0?$file:$this->urlAssets($file);
			$output[] = '<script type="text/javascript" src="'.$url.'"></script>';
		}

		// Do we have to add the minify script
		if($addMinify){
			// we retrieve the filename of the minify js
			$file = (string)$this->getApplication()->getConfig()->project->minifiy->jsfile?:'js/project.minify.js';

			// In developpement we actually load real script and set the minify in a hidden img to regenerate the minify version
			if($this->getApplication()->isDev()) {
				$output[] = '<img src="'.$this->urlAssets($file).'" width="0" height="0" style="display:none"/>';
			}else{
				$mtime = \Smally\Assets::getInstance()->getAssetMtime($file);
				$file = substr($file,0,strrpos($file, '.')) . ($mtime?'.' . $mtime:'') . '.min'. strrchr($file, '.') ;
				$preoutput[] = '<script type="text/javascript" src="'.$this->urlAssets($file).'"></script>';
			}
		}


		$js = implode(NN.TT,$preoutput).NN.implode(NN.TT,$output);

		return $js;
	}

	/**
	 * Experimental cache system begin function to call before the content you want to cache
	 * @param  string $keyPrefix A unique cache prefix key
	 * @return boolean
	 */
	protected function beginCache($keyPrefix){

		$this->cacheActive = $this->getApplication()->getFactory()->getLogic('Cms')->isCacheActive();
		$this->smallyCache = \Smally\SCache::getInstance();

		$this->caching = true;
		$this->cacheRender = null;
		$this->cacheAssets = null;
		$this->cacheKey = $keyPrefix;
		$this->cacheKeyRender = $this->smallyCache->getHashKey($keyPrefix.'_RENDER');
		$this->cacheKeyAssets = $this->smallyCache->getHashKey($keyPrefix.'_ASSETS');

		$this->topBlock = array(
			'js' => array(),
			'css' => array(),
			'meta' => array(),
			);

		return true;

	}

	/**
	 * Return whether we are already in a cache block or not ( actually we can't start a new cache in a cache , we always cache the biggest cache block)
	 * @return boolean
	 */
	public function inCacheRender(){
		return isset($this->cacheKey)&&$this->cacheKey;
	}

	/**
	 * Experimental cache system get from cache function that will either get from cache or effectively launch the cache ob
	 * @return boolean
	 */
	protected function getFromCache(){
		if($this->cacheActive){
			$render = $this->smallyCache->getKey($this->cacheKeyRender); // render must be differnt from false or null to be considered as valid cache entry
			$assets = $this->smallyCache->getKey($this->cacheKeyAssets);
			if( $render!==false && $render!==null && is_array($assets) ){
				$this->cacheRender = $render;
				$this->cacheAssets = $assets;
				return true;
			}
		}
		return false;
	}

	/**
	 * Call this function to avoid caching of the block, for example because of a sub view can't be cached
	 * @return null
	 */
	public function avoidCacheRender(){
		$this->caching = false;
		if(!is_null($this->_parentView)){
			$this->_parentView->avoidCacheRender();
		}
	}

	/**
	 * Experimental cache system end cache function that will save and output de render either from cache or just rendered
	 * @return boolean Return the expected output
	 */
	protected function endCache(){

		if(is_null($this->cacheRender)){
			$this->cacheRender = $this->getRender();
			if($this->caching) $this->smallyCache->setKey($this->cacheKeyRender,$this->cacheRender);
		}else $this->setRender($this->cacheRender);

		if(is_null($this->cacheAssets)){
			$this->cacheAssets = $this->topBlock;
			if($this->caching) $this->smallyCache->setKey($this->cacheKeyAssets,$this->cacheAssets);
		}

		$application = $this->getApplication();
		$metaHandler = $application->getMeta();
		foreach($this->cacheAssets as $type => $assets){
			switch($type){
				case 'js':
				case 'css':
					$method = 'set'.ucfirst($type);
					foreach($assets as $asset){
						$application->$method($asset);
					}
					break;
				case 'meta':
					foreach($assets as $metaType => $meta){
						$metaHandler->addMeta($metaType,$meta);
					}
					break;
			}
		}

		return $this->getRender();
	}

	/**
	 * Check if a template exist
	 * @param  string $template The template you want to test ( relative path )
	 * @return boolean
	 */
	static public function templateExist($template){
		$template = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$template);
		return (stream_resolve_include_path($template.'.php')!==false) ;
	}

	/**
	 * Execute another controller and get the generated content
	 * @param  string $controllerPath Path to the controller
	 * @param  string $action         Action to execute
	 * @param  array  $params         Params to the action (not use yet)
	 * @return string
	 */
	public function subController($controllerPath,$action='index',$params=array()){
		return $this
				->getRouter()
				->getControllerObject($controllerPath)
				->setAction($action)
				->x($params)
				->getView()
				->getRender();
	}

	/**
	 * Execute the view logic : Get the template path, start a buffer, require the template, push the buffer to the content property
	 * @return \Smally\View
	 */
	public function x($params=array()){
		$this->setRender($this->render($this->getTemplatePath(),$params));
		return $this;
	}

	public function cacheRender($cacheKey, $params=array()){
		$this->beginCache($cacheKey);
		if( !$this->getFromCache() ){
			$this->x($params);
		}
		return $this->endCache();
	}

	/**
	 * Render a particular template
	 * @param  string $template the template relative path in template folder
	 * @param  array  $params $params will be accessible in the template directly
	 * @return string
	 */
	public function render($template,$params=array()){
		$template = str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$template);
		ob_start();
		if($this->templateExist($template)){ // check if exist and fix DIRECTORY_SEPARAOTR issues
			include($template.'.php');
		}else{
			throw new Exception('Template not found : '.$template);
		}
		return ob_get_clean();
	}

}