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

	public function getItemTemplate($upload=null){

		if(is_null($this->_itemTemplate)||!is_null($upload)){

			// We get the upload element or we construct an empty one
			if(!is_null($upload)) $uploadObject = $upload;
			else $uploadObject = new \Smally\VO\Upload;

			// We have set a particular item Template
			if(!is_null($this->_itemTemplatePath)){

				$view = new \Smally\View(\Smally\Application::getInstance());
				$view->setTemplatePath($this->_itemTemplatePath);
				$view->uploadObject = $uploadObject;
				$template = $view->x()->getContent();

				if(is_null($upload)){
					$this->_itemTemplate = $template;
				}else return $template;

			}else{

				// HERE IS THE DEFAULT ITEM TEMPLATE
				$attributes = array(
					'name' => $this->getName().'[]',
					'type' => 'hidden',

				);

				if(is_null($upload)){
					$attributes['disabled'] = 'disabled';
				}

				$template = '
					<div class="file-preview'.(is_null($upload)?' jsFileTemplate':'').'" style="display:'.(is_null($upload)?'none':'block').'">
						<i class="icon-move floatRight"></i>
						<input class="id" '.\Smally\HtmlUtil::toAttributes($attributes).' value="'.$uploadObject->getId().'" />
						<h3 class="name">'._h($uploadObject->name).'</h3>
						<div class="preview"><span class="enclose"><img src="'._h($uploadObject->getUrl('thumbnail')).'" alt="upload" class="img100"/></span></div>
						<span class="size">'._h($uploadObject->size).'</span>
						<a href="'._h($uploadObject->getUrl('delete')).'" class="delete btn'.(is_null($upload)?'':' jsDeleteVo').'" data-smally-delete-parentselector=".file-preview" data-smally-delete-url="'._h($uploadObject->getUrl('delete')).'"><i class="icon-remove"></i></a>
						<a href="'._h($uploadObject->getUrl()).'" class="url btn" target="_blank"><i class="icon-zoom-in"></i></a>
					</div>
				';

				if(is_null($upload)){
					$this->_itemTemplate = $template;
				}else return $template;
			}
		}

		return $this->_itemTemplate;
	}

	public function getUploaderOptions(){
		//var_dump($this->_uploaderOptions);
		return $this->_uploaderOptions;
	}
}