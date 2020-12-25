<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginLegacyWidgetTemplateController extends waJsonController
{
    public function execute()
    {
        $template_path = wa('shop')->getConfig()->getPluginPath('sdekint') . '/js/vendors/cdek/scripts/tpl/*.html';
        $files = glob($template_path);

        foreach ($files as $file) {
            $this->response[strtolower(basename($file, '.html'))] = file_get_contents($file);
        }
    }

    public function display()
    {
        if (waRequest::isXMLHttpRequest()) {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
        }
        $this->getResponse()->sendHeaders();
        echo json_encode($this->response);
    }

}
