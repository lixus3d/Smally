<?php

namespace Smally\Form\Element;

class File extends AbstractElement{

	protected $_type = 'file';
	protected $_decorator = 'file';

	protected $_attributes = array(
			'class' => array('jsFileSelector')
		);

	protected $_application = null;

	protected $_uploaderOptions = array();
	protected $_itemTemplatePath = null;
	protected $_itemTemplate = null;
	protected $_nameUpdate = false;

	public function __construct(array $options=array()){
		parent::__construct($options);
		if($app = $this->getApplication()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/jquery-ui.min.js')
				->setJs('js/smally/jquery.fileupload.js')
				->setJs('js/smally/form/FileSelector.js')
				;
		}
	}

	public function setNameUpdate($value){
		$this->_nameUpdate = (boolean) $value;
		if($this->_nameUpdate && $app = $this->getApplication()){
			$app->setJs('js/smally/form/FileNameUpdater.js');
		}
		return $this;
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

	/**
	 * Get the current Smally Application
	 * @return \Smally\Application
	 */
	public function getApplication(){
		if(is_null($this->_application)){
			$this->_application = \Smally\Application::getInstance();
		}
		return $this->_application;
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
						<i class="icon-move floatRight jsSortableHandle"></i>
						<input class="id" '.\Smally\HtmlUtil::toAttributes($attributes).' value="'.$uploadObject->getId().'" />';
				if($this->_nameUpdate){
					$template .= '<h3><input type="text" value="'._h($uploadObject->name).'" name="uploadName" class="jsFileNameUpdate name" data-smally-updatename-url="'._h($uploadObject->getUploadUrl('updatename')).'" /></h3>';
				}else{
					$template .= '<h3 class="name">'._h($uploadObject->name).'</h3>';
				}
				$template .='

						<div class="preview"><span class="enclose"><img src="'._h($uploadObject->getUploadUrl('thumbnail')).'" alt="upload" class="img100"/></span></div>
						<span class="size">'._h($uploadObject->getReadableSize()).'</span>
						<a href="'._h($uploadObject->getUploadUrl('delete')).'" class="delete btn'.(is_null($upload)?'':' jsDeleteVo').'" data-smally-delete-parentselector=".file-preview" data-smally-delete-url="'._h($uploadObject->getUploadUrl('delete')).'"><i class="icon-remove"></i></a>
						<a href="'._h($uploadObject->getUploadUrl()).'" class="url btn" target="_blank"><i class="icon-zoom-in"></i></a>
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