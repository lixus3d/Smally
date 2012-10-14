<?php

namespace Smally\Helper\Decorator;

class Menu extends AbstractDecorator {

	/**
	 * Return the element (menu) attributes
	 * @return array
	 */
	public function getAttributes(){
		return $this->getElement()->getAttributes();
	}

	/**
	 * Render the menu decorator
	 * Usually the only one redefine in another Menu Decorator
	 * @param  string $content Existing generated content
	 * @return string
	 */
	public function render($content=''){

		if(method_exists($this, 'onRender')){
			$this->{'onRender'}();
		}

		// we render children before to adapt attributes if necessary (active class for example, or hasChildren)
		$children = $this->renderChildren();
		$html  = '<ul'.\Smally\HtmlUtil::toAttributes($this->getAttributes()).'>' . NN;
		$html .= $children;
		$html .= '</ul>' . NN;
		return $this->concat($html,$content);
	}


	/**
	 * Return the render of the current element sub elements
	 * @return string
	 */
	public function renderChildren(){
		$html = '';
		// we get the sub items
		$items = $this->getElement()->getItems();

		// we filter on visible false
		$toRender = array();
		foreach($items as $key => $item){
			// item that are invisible influence parent attributes but don't need to be rendered
			if(isset($item->visible) && $item->visible === false){
				$this->getElement()
							->getDecorator('menuElement',$item)
								->setMenu($this->getElement())
								->getAttributes(); // TODO : Change to a x function
				continue;
			}
			$toRender[] = $item;
		}

		// we render each sub items
		foreach($toRender as $key => $item){
			$html .= $this->getElement()
							->getDecorator('menuElement',$item)
								->setMenu($this->getElement())
								->setElementNumber($key,count($toRender))
								->render();
		}

		return $html;
	}
}