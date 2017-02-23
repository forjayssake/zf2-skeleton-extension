<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class RenderEmail extends AbstractHelper
{

    /**
     * return a formatted email address
     * @param string|null $email
     * @param bool $showAsLink
     * @param bool $showIcon
     * @para, string $subject - include subject query - requires $showAsLink = true
     *
     * @return string
     */
    public function __invoke($email = null, $showAsLink = true, $showIcon = true, $subject = null)
    {
        if (is_null($email) || strlen(trim($email)) == 0)
            return '';

        $emailString = ($showIcon ? '<i class="fa fa-envelope-o"></i> ' : '') . $email;

        if (!$showAsLink) {
            return $emailString;
        } else {
            return '<a href="mailto:' . $email . (!is_null($subject) ? '?subject=' . $subject : '') . '">' . $emailString . '</a>';
        }
    }

}