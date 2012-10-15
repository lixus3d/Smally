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
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'value' => $this->getElement()->getValue(),
				'data-url' => $this->getUploadUrl(),
				'multiple' => 'multiple'
			);

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input file">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/>';
		$html .= $this->getElement()->getItemTemplate();
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
}