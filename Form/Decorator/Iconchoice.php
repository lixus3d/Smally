<?php

namespace Smally\Form\Decorator;

class Iconchoice extends AbstractDecorator {

	/**
	 * Render the iconchoice Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/smally/form/Iconpicker.js')
				;
		}

		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => 'text',
				'value' => $this->getElement()->getValue(),
				'class' => 'iconchoice'
			);
		$placeholder = $this->getElement()->getPlaceholder();
		$default = $this->getElement()->getDefault();

		if($placeholder !== '') $attributes['placeholder'] = $placeholder;
		if($default !== '' && $attributes['value'] == '') $attributes['value'] = $default;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input jsIconPicker">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<input '.\Smally\Util::toAttributes($attributes).'/>';
		$html .= '<a href="#iconchoice" class="btn jsIconChoice">Choisir une icône</a>';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';

		return $this->concat($html,$content);
	}

}