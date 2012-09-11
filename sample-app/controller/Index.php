<?php

namespace Controller;

class Index extends \Smally\Controller {

	public function __construct(\Smally\Application $application){
		parent::__construct($application);
		// If you don't want to index the entire controller :
		//$this->getApplication()->getMeta()->addMetaTag(array('name'=>'robots','content'=>'nofollow, noindex, all'));
	}

	/**
	 * Home
	 * @return null
	 */
	public function indexAction(){

		// DO SOME STUFFS

	}

	public function albumsAction(){

		// HOW ABOUT GETTING ALBUMS OF YOUR MUSIC COLLECTION ?

	}


	public function regexAction(){

		// HEY, WHAT'S IN THIS ALBUM ?

	}

}
