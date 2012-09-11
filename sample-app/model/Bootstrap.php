<?php

class Bootstrap extends \Smally\AbstractBootstrap {

	public function x(){

		$this->getApplication()
			// set CSS
			->setCss('css/reset.css')
			->setCss('css/grid.less')
			->setCss('css/style.less')
			// set JS
			->setJs('js/jquery.js')
			->setJs('js/jqueryEasing.js')
			->setJs('js/global.js')
			;

		$this->getApplication()->getMeta()
			// Default METAS
			->addMeta('title','Smally - Sample Application',true)
			->addMeta('keywords','smally, smally framework, smally mvc, smally php, developpement, dev, php',true)
			->addMeta('description','Small(y) and basic Php MVC framework for quick prototyping and small projects.',true)
			->addMetaTag(array('name'=>'robots','content'=>'follow, index, all'))
			// Viewport meta for mobile and tablet devices
			->addMetaTag(array('name'=>'viewport','content'=>'width=device-width, initial-scale=1.0'))
			;

		// Define some constant for GITOPHP
		define('GIT_PATH_CMD',$this->getApplication()->getConfig()->git->paths->cmd);
		define('GIT_PATH_REPOSITORIES',$this->getApplication()->getConfig()->git->paths->repositories);
	}

}