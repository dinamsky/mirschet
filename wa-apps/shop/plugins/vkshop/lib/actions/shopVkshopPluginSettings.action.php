<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/6/16
 * Time: 12:19 PM
 */
class shopVkshopPluginSettingsAction extends waViewAction
{
    /**
     * @var shopVkshopPlugin $plugin
     */
    private static $plugin;

    public function execute()
    {
        $control_params = array(
            'id' => waRequest::get('id'),
            'namespace' => 'shop_vkshop',
            'title_wrapper' => '%s',
            'description_wrapper' => '<br><span class="hint">%s</span>',
            'control_wrapper' => '<div class="name">%s</div><div class="value">%s %s</div>'
        );

        $settings = self::$plugin->getSettings();
        $this->view->assign('settings', $settings);
        $this->view->assign('plugin_id', 'vkshop');
        $this->view->assign('tabs', shopVkshopPlugin::getTabs());
        $this->view->assign('plugin_settings_controls', $this->getPluginSettingsControls($control_params));
    }

    /**
     * shopVkshopPluginSettingsAction constructor.
     * @param null $params
     * @throws waException
     */
    public function __construct($params = null)
    {
        $plugin = wa('shop')->getPlugin('vkshop');
        self::$plugin = $plugin;
        parent::__construct($params);
    }

    /**
     * Возвращает элементы формы для вкладки Samples Settings
     *
     * @param  array $params
     * @return mixed
     */
    private function getPluginSettingsControls($params)
    {
        $controls = array(
            'basic' => self::$plugin->getControls($params + array('subject' => 'basic_settings')),
            'groups' => self::$plugin->getControls($params + array('subject' => 'groups_settings')),
            'templates' => self::$plugin->getControls($params + array('subject' => 'templates_settings')),
            'cron' => self::$plugin->getControls($params + array('subject' => 'cron_settings')),
            'info' => self::$plugin->getControls($params + array('subject' => 'info_settings')),
        );
        return $controls;
    }
}