<?php
namespace Email\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Template;

class RenderTemplateParameters extends AbstractHelper
{
    /**
     * @var array
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }


    /**
     * generate HTML to render parameters available to a given template
     *
     * @param Template $template
     *
     * @return string|null
     */
    public function __invoke(Template $template)
    {
        $event = $template->getEvent();
        if (isset($this->config[$event]))
        {
            if (isset($this->config[$event]['params']) && count($this->config[$event]['params']) > 0)
            {
                $paramsHtml = '';
                foreach($this->config[$event]['params'] as $paramName => $details)
                {
                    $paramsHtml .= $this->generateParameterString($paramName, $details);
                }

                return $paramsHtml;
            } else {
                return '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' . $this->view->translate('NO_PARAMETERS_AVAILABLE_MESSAGE') . '</div>';
            }
        }

        return null;
    }


    private function generateParameterString($paramName, array $paramDetails = [])
    {
        return '<h4><span class="label label-info tooltip-element" title="%' . $paramName . '%">'
                . $paramDetails['display'] . ' (' . $paramDetails['object']
                . ')</span></h4>';
    }


}