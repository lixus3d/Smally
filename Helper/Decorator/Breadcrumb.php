<?php

namespace Smally\Helper\Decorator;

class Breadcrumb extends AbstractDecorator {

	/**
	 * Return the element (tree) attributes
	 * @return array
	 */
	public function getAttributes(){
		return $this->getElement()->getAttributes();
	}

	/**
	 * Render the breadcrumb decorator
	 * Usually the only one redefine in another Breadcrumb Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){
		$html  = '<ul'.\Smally\Util::toAttributes($this->getAttributes()).'>' . NN;
		$html .= $this->renderPath();
		$html .= '</ul>' . NN;
		return $this->concat($html,$content);
	}


	/**
	 * Return the render of the current element path
	 * @return string
	 */
	public function renderPath(){

		$html = '';

		$path = $this->getElement()->getTree()->getPath();
		if($defaultPath = $this->getElement()->getDefaultPath()){
			$path = array_merge($defaultPath,$path);
		}

		foreach($path as $key => $item){
			$html .= $this->getElement()
							->getDecorator('breadcrumbElement',$item)
								->setBreadcrumb($this->getElement())
								->setElementNumber($key,count($path))
								->render();
		}
		return $html;
	}
}