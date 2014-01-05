<?php

namespace Smally\Form\Decorator;

class Map extends AbstractDecorator {

	/**
	 * Render the map Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'value' => $this->getElement()->getValue(),
			);
		$placeholder = $this->getElement()->getPlaceholder();
		$default = $this->getElement()->getDefault();

		if($placeholder !== '') $attributes['placeholder'] = $placeholder;
		if($default !== '' && $attributes['value'] == '') $attributes['value'] = $default;

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$value = $this->getElement()->getValue();
		$valueLat = is_array($value)&&isset($value[0])?$value[0]:'';
		$valueLng = is_array($value)&&isset($value[1])?$value[1]:'';

		$html = '<div class="input jsMap">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		// $html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/>';

		$html .= '<div class="fields-block">';
		$html .= '<input class="search jsMapSearch" type="text" name="mapsearch" value="" placeholder="'.$placeholder.'"/>';
		$html .= '<input class="lat jsMapLat" type="text" name="'.$this->getElement()->getName().'[0]" value="'.$valueLat.'" placeholder="latitude" />';
		$html .= '<input class="lng jsMapLng" type="text" name="'.$this->getElement()->getName().'[1]" value="'.$valueLng.'" placeholder="longitude" />';
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '</div>';
		$html .= '<div class="map-block" id="'.uniqid().'">';
		$html .= '</div>';
		$html .= '<hr />';
		$html .= '</div>';

		return $this->concat($html,$content);
	}

}