<?php

namespace Smally\Form\Element;

class Multiselect extends Radio{

	protected $_type = 'multiselect';
	protected $_decorator = 'select';

	public function setModeJs(){
		$this->setAttribute('class','jsMultiselect');
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/bootstrap-multiselect.js')
				->setJs('js/smally/form/Multiselect.js')
				;
		}
	}

}