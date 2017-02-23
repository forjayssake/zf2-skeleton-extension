<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class RenderLargeText extends AbstractHelper
{

    /**
     * return html to render large text strings in a scrolling div
     *
     * @param string $text text string to display
     * @param boolean $noDivOnNullValue don't return the wrapper div for an empty text value
     * @param string|null $wrapperClass an override class for the returned text wrapper
     * @param boolean $escapeHtml escape supplied text
     *
     * @return string
     */
    public function __invoke($text, $noDivOnNullValue = true, $wrapperClass = null, $escapeHtml = true)
    {
        if ($noDivOnNullValue && (is_null($text) || strlen(trim($text)) == 0))
            return '';

        $text = $escapeHtml ? $this->view->escapeHtml($text) : $text;

        $html = '<div class="generic-large-text-wrapper <?php echo $wrapperClass; ?>" id="<?php echo md5($text); ">' . $text . '</div>';
        return $html;
    }

}