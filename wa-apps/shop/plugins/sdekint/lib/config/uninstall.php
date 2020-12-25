<?php
/**
 * @package sdekint
 * @version 2.1.0
 * @copyright (c) 2016, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

$wf_config = shopWorkflow::getConfig();
$wf_sdekint = include('workflow.php'); // если (вдруг) появятся еще какие-то действия, то можно будет не беспокоиться об этом коде

$is_config_changed = false;

// если наше действие все еще есть в конфиге (вдруг пользователь его уже удалил)
foreach ($wf_sdekint['actions'] as $action_id => $action) {
    if(array_key_exists($action_id, $wf_config['actions'])) {
        // удалим действие из разрешенных у всех статусов
        foreach ($wf_config['states'] as $state_id => $state) {
            if (($key = array_search($action_id, $state['available_actions'])) !== false) {
                unset($wf_config['states'][$state_id]['available_actions'][$key]);
            }
        }
        // и из действий теперь его удалим
        unset($wf_config['actions'][$action_id]);
        $is_config_changed = true;
    }
}

if ($is_config_changed) {
    if (!shopWorkflow::setConfig($wf_config)) {
        waLog::log('Sdekintplugin Uninstall Error: cannot save changed workflow!');
    }
}
