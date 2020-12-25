<?php
$plugin_id = array('shop', 'region');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'update_time', time());
$db = new shopRegionModel();
$db->query("ALTER TABLE `".$db->table."` ADD UNIQUE INDEX `sub_domain` (`domain`(160), `subdomain`(160));");     