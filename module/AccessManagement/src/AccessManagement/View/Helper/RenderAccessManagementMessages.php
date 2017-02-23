<?php
namespace AccessManagement\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Session\Container;

class RenderAccessManagementMessages extends AbstractHelper
{
    /**
     * return flashMessenger messages for AccessManagement messages
     *  currently stored in session
     *
     * @return string|null
     */
    public function __invoke()
    {
        $messagesHtml = '';

        $containers = [
            \AccessManagementLog::ALL_USER_MESSAGE_NAMESPACE,
            \AccessManagementLog::ROLE_MESSAGE_NAMESPACE
        ];

        foreach ($containers as $namespace) {

            $container = new Container($namespace);

            if (isset($container->messages) && !empty($container->messages))
            {
                foreach ($container->messages as $key => $messageDetails)
                {
                    $messagesHtml .= $this->generateMessageHtml($messageDetails);
                }

                // kill the messages session container
                $container->getManager()->getStorage()->clear($namespace);
            }
        }

        return $messagesHtml;
    }

    /**
     * return an HTML string to render a single message
     *  'type' key must be defined in $messageDetails array to generate content
     * @param array $messageDetails
     * @return string
     */
    private function generateMessageHtml(array $messageDetails = [])
    {
        $html = '';

        if (isset($messageDetails['type'])) {

            $html .= '<div class="alert alert-' . $messageDetails['type'] . '"> ';

            switch ($messageDetails['type']) {
                case 'warning' :
                    $html .= '<i class="fa ' . \AccessManagementLog::WARNING_MESSAGE_ICON . '"></i> ';
                    break;
                case 'danger' :
                    $html .= '<i class="fa ' . \AccessManagementLog::ERROR_MESSAGE_ICON . '"></i> ';
                    break;
                case 'info' :
                default :
                    $html .= '<i class="fa ' . \AccessManagementLog::INFO_MESSAGE_ICON . '"></i> ';
                    break;
            }

            $html .= $messageDetails['message'] . '</div>';
        }

        return $html;
    }

}