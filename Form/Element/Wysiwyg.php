<?php

namespace Smally\Form\Element;

class Wysiwyg extends Textarea{

	protected $_type = 'textarea';
	protected $_decorator = 'textarea';

	protected $_attributes = array(
			'class' => array('jsWysiwyg'),
		);

	public function __construct(array $options=array()){
		parent::__construct($options);
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/tiny_mce/jquery.tinymce.js')
				->setJs('js/tiny_mce/tiny_mce.js')
				->setJs('js/smally/form/Wysiwyg.js')
				;
		}
	}

}