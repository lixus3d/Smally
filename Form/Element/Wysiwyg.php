<?php

namespace Smally\Form\Element;

class Wysiwyg extends Textarea{

	protected $_type = 'textarea';
	protected $_decorator = 'textarea';

	protected $_attributes = array(
			'class' => array('jsWysiwyg'),
		);

	public function init(){
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs($app->getBaseUrl('assets/js/tiny_mce/jquery.tinymce.js'))
				->setJs($app->getBaseUrl('assets/js/tiny_mce/tiny_mce.js'))
				->setJs($app->getBaseUrl('assets/js/smally/form/Wysiwyg.js'))
				;
		}
	}

}