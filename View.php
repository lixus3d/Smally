<?php

namespace Smally;

class View {

	protected $_application = null;

	protected $_templatePath = null;

	protected $_content = '';

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
	 * Define the view template path
	 * @param string $templatePath the template path
	 */
	public function setTemplatePath($templatePath){
		$this->_templatePath = $templatePath;
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
	 * Return the template path of this view
	 * @return string
	 */
	public function getTemplatePath(){
		return $this->_templatePath;
	}

	/**
	 * Get the content of the view, default empty
	 * @return string
	 */
	public function getContent(){
		return $this->_content;
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
				$output[] = '<meta'.\Smally\HtmlUtil::toAttributes($element).'/>';
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
		$cssFiles = $this->getApplication()->getCss();
		foreach($cssFiles as $file){
			if(strpos($file,'.less') > 0 && !$this->getApplication()->isDev()) {
				$file .= '.css';
			}
			$url = strpos($file,'http')===0?$file:$this->urlAssets($file);
			$output[] = '<link rel="stylesheet"  type="text/css"  media="all" href="'.$url.'"/>';
		}
		return implode(NN.TT,$output);
	}

	/**
	 * Return the html tags of the js files
	 * @return string
	 */
	public function getJs(){
		$output = array();
		$jsFiles = $this->getApplication()->getJs();
		foreach($jsFiles as $file){
			$url = strpos($file,'http')===0?$file:$this->urlAssets($file);
			$output[] = '<script type="text/javascript" src="'.$url.'"></script>';
		}
		return implode(NN.TT,$output);
	}

	/**
	 * Get a controller url from his $path and $params
	 * @param  string $controllerPath The controller path ( with action )
	 * @param  array  $params         Array of $key => $value to put in the generated $url
	 * @return string
	 */
	public function getControllerUrl($controllerPath,$params=array()){
		return $this->getApplication()->getBaseUrl($this->getApplication()->makeControllerUrl($controllerPath,$params));
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
				->getRooter()
				->getControllerObject($controllerPath)
				->setAction($action)
				->x($params)
				->getView()
				->getContent();
	}

	/**
	 * Execute the view logic : Get the template path, start a buffer, require the template, push the buffer to the content property
	 * @return \Smally\View
	 */
	public function x($params=array()){
		$this->_content = $this->render($this->getTemplatePath(),$params);
		return $this;
	}

	/**
	 * Render a particular template
	 * @param  string $template the template relative path in template folder
	 * @param  array  $params $params will be accessible in the template directly
	 * @return string
	 */
	public function render($template,$params=array()){
		$template = str_replace('\\','/',$template);
		$templatePath = 'template'.DIRECTORY_SEPARATOR.$template.'.php';
		ob_start();
		if(!include(ROOT_PATH.$templatePath)){
			throw new Exception('Template not found : '.$template);
		}
		return ob_get_clean();
	}

}