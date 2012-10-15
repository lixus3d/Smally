<?php

namespace Smally\Form\Element;

class File extends AbstractElement{

	protected $_type = 'file';
	protected $_decorator = 'file';

	protected $_attributes = array(
			'class' => array('jsFileSelector')
		);

	protected $_uploaderOptions = array();
	protected $_itemTemplatePath = null;
	protected $_itemTemplate = null;

	public function __construct(array $options=array()){
		parent::__construct($options);
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/jquery-ui.min.js')
				->setJs('js/smally/jquery.fileupload.js')
				->setJs('js/smally/form/FileSelector.js')
				;
		}
	}

	public function setUploaderOption($optionName,$optionValue){
		$this->_uploaderOptions[$optionName] = $optionValue;
		return $this;
	}

	public function setItemTemplatePath($templatePath){
		$this->_itemTemplatePath = $templatePath;
		return $this;
	}

	public function setItemTemplate($template){
		$this->_itemTemplate = $template;
		return $this;
	}

	public function getItemTemplate(){
		if(is_null($this->_itemTemplate)){
			if(!is_null($this->_itemTemplatePath)){
				$view = new \Smally\View(\Smally\Application::getInstance());
				$view->setTemplatePath($this->_itemTemplatePath);
				$this->_itemTemplate = $view->x()->getContent();
			}else{
				$attributes = array(
					'name' => $this->getName(),
					'type' => 'hidden',
					'disabled' => 'disabled'
				);
				$this->_itemTemplate = '
					<div class="file-preview jsFileTemplate" style="display:none">
						<i class="icon-move floatRight"></i>
						<input class="id" '.\Smally\HtmlUtil::toAttributes($attributes).'/>
						<h3 class="name"></h3>
						<div class="preview"></div>
						<span class="size"></span>
						<a href="#" class="delete btn" data-smally-delete-parentselector=".jsFileTemplate" data-smally-delete-url="#"><i class="icon-remove"></i></a>
						<a href="#" class="url btn" target="_blank"><i class="icon-zoom-in"></i></a>
					</div>
				';
			}
		}
		return $this->_itemTemplate;
	}

	public function getUploaderOptions(){
		//var_dump($this->_uploaderOptions);
		return $this->_uploaderOptions;
	}
}