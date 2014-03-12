<?php

namespace Smally\Form\Element;

class TagList extends Radio{

	protected $_type = 'text';
	protected $_decorator = 'taglist';

	protected $_voName = null;

	protected $_flatValue = null;

	public function init(){
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/jquery-ui.min.js')
				->setCss('css/jqueryui-adn-theme/jquery-ui-1.8.24.custom.css')
				->setJs('js/smally/form/TagList.js')
				;
		}
	}

	/**
	 * Define the vo name of the sub item
	 * @param string $voName The vo name of the sub item you want to add
	 * @return  \Smally\Form\Element\TagList
	 */
	public function setVoName($voName){
		$this->_voName = $voName;
		return $this;
	}

	/**
	 * Return the vo name of the sub item
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	public function getValue(){
		if(!is_null($this->_flatValue)) return $this->_flatValue;
		$return = '';
		if($this->getChecked() && $app = \Smally\Application::getInstance()){
			$dao = $app->getFactory()->getDao($this->getVoName());
			$criteria = $dao->getCriteria();
			$criteria ->setFilter(array($dao->getPrimaryKey()=>array('value'=>$this->getChecked(),'operator'=>'IN')));
			if($list = $dao->fetchAll($criteria)){
				foreach($list as $vo){
					$return .= $vo->getName().', ';
				}
			}
		}
		return $return;
	}

	/**
	 * Reset the field state
	 * @return \Smally\Form\Element\TagList
	 */
	public function resetValue(){
		$this->_checked = array();
		return $this; // parent::resetValue();
	}

	public function populateValue($values){
		// TODO : A bit tricky to verify coming from a form
		if(is_string($values)){
			$this->_flatValue = $values;
		}else{
			if($values instanceof \Smally\ContextStdClass) $values = $values->toArray();
			if(!is_array($values)) $values = array($values);
			foreach($values as $value){
				$this->_checked[] = $value;
			}
		}
		return $this;
	}
}