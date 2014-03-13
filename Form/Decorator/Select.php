<?php

namespace Smally\Form\Decorator;

class Select extends AbstractDecorator {

	/**
	 * Render the radio Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$type = $this->getElement()->getType(); // radio or checkbox

		$html = '<div class="input">';

		if($type == 'multiselect'){
			$html.= '<input type="hidden" name="'.$this->getElement()->getName().'" value="" />';
		}

		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);

		$attributes = array(
				'name' => $this->getElement()->getName() . ( $type=='multiselect'?'[]':'') ,
			);

		$placeholder = $this->getElement()->getPlaceholder();
		if($placeholder !== '') $attributes['placeholder'] = $placeholder;

		if($type=='multiselect') $attributes['multiple'] = 'multiple';

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html .= '<select '.\Smally\Util::toAttributes($attributes).'>';

		if( !is_array($this->getElement()->getValue()) ) $values = array($this->getElement()->getValue()=>$this->getElement()->getValue());
		else $values = $this->getElement()->getValue();

		foreach($values as $value => $label){
			$attributes = array(
				'value' => $value,
			);
			if(in_array($value,$this->getElement()->getChecked())){
				$attributes['selected'] = 'selected';
			}
			$html .= '<option '.\Smally\Util::toAttributes($attributes).'>'.$label.'</option>';
		}
		$html .= '</select>';

		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);

		$html .= '</div>';

		return $this->concat($html,$content);
	}

}