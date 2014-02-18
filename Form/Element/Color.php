<?php

namespace Smally\Form\Element;

class Color extends Text{

	protected $_type = 'text';
	protected $_decorator = 'color';

	protected $_colorPicker = null;

	public function setColorPicker($colorList){
		$this->_colorPicker = $colorList;
		return $this;
	}

	public function getColorPicker(){
		return $this->_colorPicker;
	}

}