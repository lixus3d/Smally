<?php

namespace Smally;

class View {

	protected $_application = null;

	protected $_templatePath = null;

	protected $_content = '';

	/**
	 * Construct the global $context object
	 * @author Lixus3d <developpement@adreamaline.com>
	 */
	public function __construct(\Smally\Application $application){
		$this->setApplication($application);
	}

	public function setApplication(\Smally\Application $application){
		$this->_application = $application;
		return $this;
	}

	public function setTemplatePath($templatePath){
		$this->_templatePath = $templatePath;
	}

	public function __call($name,$args){
		if(method_exists($this->getApplication(), $name)){
			return call_user_func_array(array($this->getApplication(),$name), $args);
			//return $this->getApplication()->$name($args);
		}else throw new Exception('Call to undefined method : '.$name);
		return null;
	}

	public function __get($name){
		return null;
	}

	public function getApplication(){
		return $this->_application;
	}

	public function getTemplatePath(){
		return 'template'.DIRECTORY_SEPARATOR.$this->_templatePath.'.php';
	}

	public function getContent(){
		return $this->_content;
	}

	/**
	 * Return the content of a meta type
	 * @param string $type
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
				$output[] = '<meta'.\EG\HtmlUtil::toAttributes($element).'/>';
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
			if(strpos($file,'.less') > 0) {
				$file .= '.css';
			}
			$url = strpos($file,'http')===0?$file:$this->getBaseUrl('assets/'.$file);
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
			$output[] = '<script type="text/javascript" src="'.$this->getBaseUrl('assets/'.$file).'"></script>';
		}
		return implode(NN.TT,$output);
	}


	/**
	 * Proxy to the front controller action doing
	 * @param string $actionPath
	 * @return string
	 */
	public function subController($controllerPath,$action='index',$params=array()){

		return $this
				->getRooter()
				->getControllerObject($controllerPath)
				->setAction($action)
				->x()
				->getView()
				->getContent();
	}


	public function x(){
		$template = $this->getTemplatePath();
		if(file_exists(ROOT_PATH.$template)){
			ob_start();
			require(ROOT_PATH.$template);
			$this->_content = ob_get_clean();
		}else throw new Exception('Template not found : '.$template);
		return $this;
	}
}