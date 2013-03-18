<?php

namespace Smally\Form\Decorator;

class TagList extends AbstractDecorator {

	/**
	 * Render the radio Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$application = \Smally\Application::getInstance();

		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'value' => $this->getElement()->getValue(),
				'class' => 'taglist',
				'data-smally-search-url' => $application->getBaseUrl($application->makeControllerUrl('Administration\\GenericRpc\\search',array('voName'=>$this->_element->getVoName()))),
			);
		$placeholder = $this->getElement()->getPlaceholder();
		$default = $this->getElement()->getDefault();

		if($placeholder !== '') $attributes['placeholder'] = $placeholder;
		if($default !== '' && $attributes['value'] == '') $attributes['value'] = $default;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input taglist">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);

	}

}