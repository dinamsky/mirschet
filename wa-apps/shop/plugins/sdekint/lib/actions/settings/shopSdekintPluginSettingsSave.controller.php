<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

class shopSdekintPluginSettingsSaveController extends waJsonController
{
    protected $settings = [];

    /** @var shopSdekintPlugin */
    protected $plugin;

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        if (!$this->isPostRequest()) {
            throw new waException('Only POST', 403);
        }

        $settings = $this->getRequest()->post('settings');
        if (!is_string($settings)) {
            throw new waException('Invalid data');
        }

        $this->settings = waUtils::jsonDecode($settings, true);
        if (!is_array($this->settings) || empty($this->settings)) {
            throw new waException('Invalid data');
        }

        $this->plugin = wa('shop')->getPlugin('sdekint');

        parent::preExecute();
    }

    public function execute()
    {
        try {
            $this->plugin->saveSettings($this->settings);
            $this->response = ['message' => 'Записано'];
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

    protected function isPostRequest()
    {
        return $this->getRequest()->method() === 'post';
    }
}