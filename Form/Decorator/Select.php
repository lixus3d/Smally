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

		$attributes = array(
				'name' => $this->getElement()->getName() . ( $type=='multiselect'?'[]':'') ,
			);
		if($type=='multiselect') $attributes['multiple'] = 'multiple';

		$html .= '<select '.\Smally\HtmlUtil::toAttributes($attributes).'>';

		if( !is_array($this->getElement()->getValue()) ) $values = array($this->getElement()->getValue()=>$this->getElement()->getValue());
		else $values = $this->getElement()->getValue();

		foreach($values as $value => $label){
			$attributes = array(
				'value' => $value,
			);
			if(in_array($value,$this->getElement()->getChecked())){
				$attributes['selected'] = 'selected';
			}
			$html .= '<option '.\Smally\HtmlUtil::toAttributes($attributes).'>'.$label.'</option>';
		}
		$html .= '</select>';

		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);

		$html .= '</div>';

		return $this->concat($html,$content);
	}

}