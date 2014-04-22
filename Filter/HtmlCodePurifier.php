<?php

namespace Smally\Filter;

class HtmlCodePurifier extends AbstractRule {

	public $config = null;

	public function __construct(){
		require_once VENDORS_PATH.'/htmlpurifier/HTMLPurifier.standalone.php';
		$this->config = \HTMLPurifier_Config::createDefault();
		$this->config->set('HTML.Allowed', 'p,b,a[href|target],i,em,strong');
		$this->config->set('Attr.AllowedFrameTargets', array('_blank'));
		$this->config->set('URI.Base', \Smally\Application::getInstance()->getBaseUrl());
		$this->config->set('URI.MakeAbsolute', true);
		$this->config->set('AutoFormat.AutoParagraph', true);
	}

	/**
	 * Filter the $value to be valid html and clean XSS
	 * @param  mixed $value
	 * @return boolean
	 */
	public function x($value){
		if(is_array($value)) $value = array_shift($value);
		if(is_null($value)) return null;


		$purifier = new \HTMLPurifier($this->config);
		$value = $purifier->purify( $value );

		return $value;
	}

}