<?php

namespace Smally\Form\Decorator;

class Submodel extends AbstractDecorator {


	/**
	 * Render the input Decorator, work for type : text, password, checkbox, radio, submit
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content){

		$app = \Smally\Application::getInstance();
		$voName = $this->getElement()->getVoName();
		$form = $app->getFactory()->getForm($voName);


		$formFields = $form->getFields();
		$wantedFields = $this->getElement()->getVoFields();

		$html = '<div class="input submodel">';
		$html .= '<a href="#" class="submodel-add">Ajouter un autre élément</a>';
		foreach($this->getElement()->getValue() as $k => $valueVo){
			$form->setNamePrefix($this->getElement()->getName().'['.$k.']');
			$form->populateValue($valueVo->toArray());
			$line = '<div class="submodel-line line-'.$k.'">';
			foreach($formFields as $field){
				if(in_array($field->getName(false),$wantedFields)){
					$line .= $field->render();
				}
			}
			$line .= '<hr />';
			$line .= '</div>';
			$html .= $line;
		}

		// $html .= $lines;
		$html .= '</div>';

		/*
		$attributes = array(
				'name' => $this->getElement()->getName(),
				'type' => $this->getElement()->getType(),
				'data-url' => $this->getUploadUrl(),
				'multiple' => 'multiple'
			);

		$uploadsHtml = $this->renderUploads();

		$attributes = array_merge($attributes,$this->_element->getAttributes());

		$html = '<div class="input file">';
		$html  = $this->getForm()->getDecorator('error',$this->_element)->render($html);
		$html .= '<input '.\Smally\HtmlUtil::toAttributes($attributes).'/> <span class="html5-browser">Glisser-déposer vos fichiers ici</span>';
		$html .= '<hr />';
		$html .= $this->getElement()->getItemTemplate();
		$html .= $uploadsHtml;
		$html  = $this->getForm()->getDecorator('help',$this->_element)->render($html);
		$html .= '<hr />';
		$html .= '</div>';
		*/

		return $this->concat($html,$content);
	}


}