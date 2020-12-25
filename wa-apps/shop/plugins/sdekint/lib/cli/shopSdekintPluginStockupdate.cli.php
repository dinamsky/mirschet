<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2015
 * @deprecated 3.0.0
 */
class shopSdekintPluginStockupdateCli extends waCliController
{
    /** @var shopSdekintPlugin */
    private $plugin = null;

    /** @var shopSdekintPluginSdekApi */
    private $SdekApi = null;

    /** @var waAppSettingsModel */
    private $AppSetting;

    public function execute()
    {
        try {
            shopSdekintPluginHelper::checkRequiredExtensions();
        } catch (waException $e) {
            shopSdekintPluginHelper::log($e->getMessage(), true);
            return;
        }

        shopSdekintPluginHelper::log('Использование консольного задания stockupdate устарело. Смотрите вкладку Информация в настройках плагина', true);
    }
}
