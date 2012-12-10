<?php

namespace Smally\Form\Decorator;

class Label extends AbstractDecorator{

	/**
	 * Render the label Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$html = '';

		if($label = $this->getElement()->getLabel()){
			$labelAdd = '';
			// if we have a validator assign to the form
			if($validator = $this->getElement()->getForm()->getValidator()){
				//echo $this->getElement()->getName(false);
				if( $rules = $validator->getFieldRules($this->getElement()->getName(false)) ){
					foreach($rules as $rule){
						$labelAdd .= $rule->getLabelAdd();
					}
				}
			}
			$html .= '<div class="inputLabel">';
			$html = $this->getForm()->getDecorator('comment',$this->_element)->render($html);
			$html .= '<label for="'.$this->getElement()->getName().'">'.$label.$labelAdd.'</label>';
			$html .= '</div>';
		}

		return $this->concat($html,$content);
	}
}