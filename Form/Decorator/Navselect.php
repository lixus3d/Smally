<?php

namespace Smally\Form\Decorator;

class Navselect extends AbstractDecorator {

	/**
	 * Render the select Decorator specific for navselect
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$type = $this->getElement()->getType(); // select / multiselect

		$html = '<div class="input">';

		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);

		$attributes = array(
				'name' => $this->getElement()->getName() . ( $type=='multiselect'?'[]':'') ,
			);

		$placeholder = $this->getElement()->getPlaceholder();
		if($placeholder !== '') $attributes['placeholder'] = $placeholder;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html .= '<select '.\Smally\Util::toAttributes($attributes).'>';

		if( !is_array($this->getElement()->getValue()) ) $values = array($this->getElement()->getValue()=>$this->getElement()->getValue());
		else $values = $this->getElement()->getValue();

		foreach($values as $value => $options){
			if($partHtml = $this->getPartHtml($value,$options)){
				$html .= $partHtml;
			}

		}
		$html .= '</select>';

		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);

		$html .= '</div>';

		return $this->concat($html,$content);
	}


	/**
	 * Get part html of on option or optgroup
	 * @param  int  $value   the value of the option
	 * @param  array  $options name,children,selectable
	 * @param  integer $level   Level of the part , for indenting
	 * @return string
	 */
	public function getPartHtml($value,$options,$level=0){

		if($level>1){
			$prefix = str_pad('',($level-1)*2,'-',STR_PAD_LEFT).'| ';
		}else{
			$prefix = '';
		}

		$partHtml = '';
		if(!is_array($options)){
			$options = array(
				'name' => $options,
				'selectable' => true,
				'children' => array(),
			);
		}
		$attributes = array(
			'value' => $value,
		);
		if($options['selectable'] == false){ // option group
			if(isset($options['children']) && $options['children']){
				$partHtml .= '<optgroup label="'.$prefix.$options['name'].'">';
				foreach($options['children'] as $subValue => $subOptions){
					if($subPart = $this->getPartHtml($subValue,$subOptions,$level+1)){
						$partHtml .= $subPart;
					}
				}
				$partHtml .= '</optgroup>';
			}
		}else{ // normal option
			if(in_array($value,$this->getElement()->getChecked())){
				$attributes['selected'] = 'selected';
			}
			$partHtml .= '<option '.\Smally\Util::toAttributes($attributes).'>'.$prefix.$options['name'].'</option>';
			if(isset($options['children']) && $options['children']){
				foreach($options['children'] as $subValue => $subOptions){
					if($subPart = $this->getPartHtml($subValue,$subOptions,$level+1)){
						$partHtml .= $subPart;
					}
				}
			}
		}

		return $partHtml;
	}

}