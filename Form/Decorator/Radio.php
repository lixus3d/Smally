<?php

namespace Smally\Form\Decorator;

/**
 * Decorator for Radio and Checkbox element
 */
class Radio extends AbstractDecorator {

	/**
	 * Render the radio Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$type = $this->getElement()->getType(); // radio or checkbox

		$html = '<div class="input">';

		if($type == 'checkbox'){
			$html.= '<input type="hidden" name="'.$this->getElement()->getName().'" value="" />';
		}

		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);

		$html .= '<ul>';

		if( !is_array($this->getElement()->getValue()) ) $values = array($this->getElement()->getValue()=>$this->getElement()->getValue());
		else $values = $this->getElement()->getValue();

		foreach($values as $value => $label){
			$attributes = array(
				// 'name' => $this->getElement()->getName().'[]',
				//'name' => $this->getElement()->getName(),
				'type' => $type,
				'value' => $value,
			);
			if($type == 'radio'){
				$attributes['name'] = $this->getElement()->getName();
			}else{
				$attributes['name'] = $this->getElement()->getName().'[]';
			}
			if(in_array($value,$this->getElement()->getChecked())){
				$attributes['checked'] = 'checked';
			}
			$html .= '<li class="'.$type.'-element"><label class="'.$type.'"><input '.\Smally\HtmlUtil::toAttributes($attributes).'/> <span class="'.$type.'-label">'.$label.'</span></label></li>';
		}

		$html .= '</ul>';

		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);


		$html .= '</div>';

		return $this->concat($html,$content);
	}

}