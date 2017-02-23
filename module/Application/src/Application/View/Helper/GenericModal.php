<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GenericModal extends AbstractHelper
{
	
	/**
	 * return html to render a modal window 
	 * @param string $id id for modal element
	 * @Param string $headerText translation string to display in modal header
	 * @param array $options [
	 * 							'buttons' = [ string, string ... ],
	 * 							'modalClass' = string
	 * 						]
	 * @param string $partial - body partial
	 * @param array $partialParams - array of params required by $partial
	 */
	public function __invoke($id, $headerText = null, array $options = [], $partial = null, array $partialParams = [])
	{
		return $this->view->partial('partial/modal', 
									[
										'id' => $id, 
										'header' => $this->view->translate($headerText), 
										'options' => $options, 
										'partial' => $partial, 
										'partialParams' => $partialParams
									]
								);
	}
	
}