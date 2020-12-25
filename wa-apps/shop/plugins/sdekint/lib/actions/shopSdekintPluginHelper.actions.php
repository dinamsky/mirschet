<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginHelperActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('sdekint');
    }

    /**
     *
     */
    public function calcAction()
    {
        try {
            $this->response = $this->plugin->helper()->calc(
                (array)waRequest::get('data', array(), waRequest::TYPE_ARRAY)
            );
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     *
     */
    public function cityAction()
    {
        try {
            $this->response = $this->plugin->helper()->city(
                (array)waRequest::get('data', array(), waRequest::TYPE_ARRAY)
            );
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     *
     */
    public function pointsAction()
    {
        try {
            $this->response = $this->plugin->helper()->points(
                (array)waRequest::get('data', array(), waRequest::TYPE_ARRAY)
            );
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
        }
    }
}
