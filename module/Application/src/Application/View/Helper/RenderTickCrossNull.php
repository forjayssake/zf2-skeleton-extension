<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception\InvalidArgumentException;

class RenderTickCrossNull extends AbstractHelper
{
	
	/**
	 * return html to render a bootstrap/fontawesome tick/cross badge 
	 * @param mixed $value 0/1, true/false, null
	 */
	public function __invoke($value)
	{
		switch($value)
		{
			case null: 
			case false:
			case 0:
				$content = false;
				break;
			case 1:
			case true:
				$content = true;
				break;
			default:
				throw new InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . ' Says: Input value must be true/false, 1/0, or null');
		}
		
		return '<span class="badge alert-' . ($content ? 'success' : 'danger') . '"><i class="fa fa-' . ($content ? 'check' : 'remove') . '"></i></span>';
	}
	
	
	
}