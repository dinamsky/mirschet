<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

/**
 * Class shopSdekintPluginCalculatorActions
 */
class shopSdekintPluginCalculatorActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /**
     *
     */
    public function defaultAction()
    {
        $view = wa()->getView();
        $sender_city = $this->plugin->sender_city;
        $view->assign(compact('sender_city'));
        $this->response['html'] = $view->fetch($this->getTemplate('Calculator'));
    }

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('sdekint');
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }
}
