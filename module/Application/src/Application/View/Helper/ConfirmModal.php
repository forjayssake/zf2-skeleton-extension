<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ConfirmModal extends AbstractHelper
{
	/**
	 * return html to render a confirmation modal
	 * @param string $id
	 * @param string $header
	 * @param string $bodyPartial
	 * @param array $partialParams
	 */
	public function __invoke($id, $headerText, array $options = [], $partial = null, array $partialParams = [])
	{
		$buttons = ['<button class="btn btn-primary generic-modal-confirm"><i id="generic-modal-confirm-icon" class=""></i> '.$this->view->translate('MODAL_CONFIRM').'</button>',
					'<button class="btn btn-default generic-modal-cancel">'.$this->view->translate('MODAL_CANCEL').'</button>'];
		
		$options = ['buttons' => $buttons];
		
		$this->view->headScript()->appendFile('/js/confirm-modal-helper.js');
		
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
