<?php

namespace Smally\Form\Element;

class Date extends AbstractElement{

	protected $_type = 'text';

	protected $_attributes = array(
			'class' => array('jsDateSelector'),
			'data-form-defaultValue' => 'JJ/MM/YYYY'
		);

	public function __construct(array $options=array()){
		parent::__construct($options);
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/jquery-ui.min.js')
				->setCss('css/jqueryui-adn-theme/jquery-ui-1.8.24.custom.css')
				->setJs('js/form/DefaultValue.js')
				->setJs('js/form/DateSelector.js')
				;
		}
	}
}