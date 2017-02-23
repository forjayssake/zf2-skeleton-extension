<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ShowFlashMessages extends AbstractHelper
{
	/**
	 * message to render
	 * @var array
	 */
	protected $messages = [];
	
	protected $allowedTypes = [
		'error',
		'warning',
		'success',
		'info'
	];
	
	/**
	 * Render flashMessages
	 * 
	 * @param string $type
	 * @param mixed $messages
	 * 
	 * @return string 
	 */
	public function __invoke($type, $messages)
	{
		if(!is_array($messages))
			$messages = array($messages);
		
		if(count($messages) === 0)
			return '';

		$type = strtolower($type);
		
		if (!in_array($type, $this->allowedTypes))
			return '';
		
		$this->messages = $messages;
		
		return $this->renderMessages($type);
	}

	/**
	 * Render error messages as Bootstrap styled message
	 * @param string $type message type aligning with bootstrap alert-type class 
	 * @param array $messages
	 * 
	 * @return string
	 */
	private function renderMessages($type)
	{
		$html = '';
		
		if ($type == 'error')
			$type = 'danger';
		
		foreach($this->messages as $message)
		{
			$html .= '<div class="alert alert-' . $type . ' alert-temporary">
						<button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
		}
		
		return $html;
	}
}