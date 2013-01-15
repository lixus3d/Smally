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
		// Get the submodel vo
		$voName = $this->getElement()->getVoName();
		// Get the default form for the submodel
		$form = $app->getFactory()->getForm($voName);
		// Get fields of the form
		$formFields = $form->getFields();
		// Sub form will use placeholder instead of label
		foreach($formFields as &$field){
			$label = $field->getLabel();
			$field->setLabel('');
			$field->setPlaceholder($label);
		}
		$wantedFields = $this->getElement()->getVoFields();

		$html = '<div class="input submodel jsSubmodel">';

		$html .= '<a href="#" class="submodel-add">'.$this->getElement()->getAddLabel().'</a>';

		$value = $this->getElement()->getValue();
		$value[] = new $voName();

		foreach($value as $k => $valueVo){
			// Each line must have it's own prefix
			$form->setNamePrefix($this->getElement()->getName().'['.$k.']');
			// We populate each line with correct values if actual $vo
			if($k < count($value)-1){
				$form->populateValue($valueVo->toArray());
			}else{
				$form->populateValue($valueVo);
			}



			$line = '<div class="submodel-line">';
			$line .= '<input type="hidden" name="'.$form->getNamePrefix().'['.$valueVo->getPrimaryKey().']" value="'.$valueVo->getId().'" />';
			if($this->getElement()->isOrder()){
				$line .= '<a href="#" class="btn btn-small floatLeft submodel-order"><i class="icon-resize-vertical"></i></a>';
			}
			foreach($formFields as &$field){
				if(in_array($field->getName(false),$wantedFields)){
					$line .= $field->render();
				}
			}
			$line .= '<a href="#"
			class="btn btn-danger btn-small jsDeleteVo"
			data-smally-delete-parentselector=".submodel-line"
			data-smally-delete-url="'.$valueVo->getUrl('Administration\\GenericRpc\\delete',array('voName'=>'Cucina\\VO\\Date')).'"
			>
			<i class="icon-remove icon-white"></i>
		</a>';
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