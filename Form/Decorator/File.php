<?php

namespace Smally\Form\Decorator;

class File extends AbstractDecorator {

	protected $_uploadUrl = null ;

	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){


		$attributes = array(
				'name' => 'Upload['.$this->getElement()->getName(false).']',
				'type' => $this->getElement()->getType(),
				'data-url' => $this->getUploadUrl(),
				'multiple' => 'multiple'
			);

		$uploadsHtml = $this->renderUploads();

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input file">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/> <span class="html5-browser">Glisser-déposer vos fichiers ici</span>';
		$html .= '<hr />';
		$html .= $this->getElement()->getItemTemplate();
		$html .= $uploadsHtml;
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '<hr />';
		$html .= '</div>';

		return $this->concat($html,$content);
	}

	public function getUploadUrl(){
		if(is_null($this->_uploadUrl)){
			$application = \Smally\Application::getInstance();

			$options = $this->getElement()->getUploaderOptions();
			$params = array(
					'options' => $options,
					'oKey' => \Smally\Uploader::generateOptionsKey($options)
				);

			$this->_uploadUrl = $application->getBaseUrl($application->makeControllerUrl('Upload\\add',$params));
		}
		return $this->_uploadUrl;
	}

	public function renderUploads(){
		$html = '';
		if(is_array($this->getElement()->getValue()) && $uploads = $this->getElement()->getValue()){
			$application = \Smally\Application::getInstance();
			foreach($uploads as $uploadId){
				if($upload = $application->getFactory()->getDao('Smally\\VO\\Upload')->getById($uploadId)){
					$html .= $this->getElement()->getItemTemplate($upload);
				}
			}
		}
		return $html;
	}
}