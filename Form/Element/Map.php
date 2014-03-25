<?php

namespace Smally\Form\Element;

class Map extends AbstractElement{

	protected $_type = 'text';
	protected $_decorator = 'map';

	public function init(){
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('https://maps.googleapis.com/maps/api/js?key='.((string)$app->getConfig()->googlemap->key?:'').'&sensor=false')
				->setJs($app->urlAssets('js/smally/form/Map.js'))
				;
		}
		if(!$this->getPlaceholder()){
			$this->setPlaceholder(__('FORM_DECORATOR_MAP_PLACEHOLDER'));
		}
		if(!$this->getHelp()){
			$this->setHelp(__('FORM_DECORATOR_MAP_HELP'));
		}
	}
}