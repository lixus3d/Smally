<?php

namespace Smally\Form\Element;

class Submodel extends AbstractElement{

	protected $_type = 'submodel';
	protected $_decorator = 'submodel';

	protected $_voName = null;
	protected $_voFields = array();
	protected $_checked = array();
	protected $_isOrder = false;
	protected $_addLabel = 'Ajouter un élément';

	public function init(){
		if($app = \Smally\Application::getInstance()){
			$app
				->setJs('js/jquery.min.js')
				->setJs('js/jquery-ui.min.js')
				->setCss('css/jqueryui-adn-theme/jquery-ui-1.8.24.custom.css')
				->setJs('js/smally/vo/DeleteVo.js')
				->setJs('js/smally/form/Submodel.js')
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

	public function setVoFields($fields){
		$this->_voFields = $fields;
		return $this;
	}

	public function setIsOrder($state){
		$this->_isOrder = (boolean) $state;
		return $this;
	}

	public function setAddLabel($addLabel){
		$this->_addLabel = $addLabel;
		return $this;
	}

	public function isOrder(){
		return $this->_isOrder;
	}

	/**
	 * Return the selected/checked options of the Field
	 * @return array
	 */
	public function getChecked(){
		return $this->_checked;
	}

	/**
	 * Return the vo name of the sub item
	 * @return string
	 */
	public function getVoName(){
		return $this->_voName;
	}

	public function getVoFields(){
		return $this->_voFields;
	}

	public function getAddLabel(){
		return $this->_addLabel;
	}

	public function getValue(){
		$return = array();
		if($this->getChecked() && $app = \Smally\Application::getInstance()){
			$voName = $this->getVoName();
			foreach($this->getChecked() as $vars){
				$vo = new $voName($vars);
				$return[] = $vo;
			}
		}
		return $return;
	}

	/**
	 * Reset the field state
	 * @return \Smally\Form\Element\Submodel
	 */
	public function resetValue(){
		$this->_checked = array();
		return $this ; //parent::resetValue();
	}

	public function populateValue($values){
		if($values instanceof \Smally\ContextStdClass) $values = $values->toArray();
		if(!is_array($values)) $values = array($values);
		foreach($values as $value){
			// don't populate with empty value values (:))
			$ok = false;
			foreach($value as $key => $v){
				if($v!==''&&$v!=='0:0'){
					$ok = true;
					break;
				}
			}
			if($ok){
				$this->_checked[] = $value;
			}
		}
		return $this;
	}

}