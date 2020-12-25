<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.1.0
 * @copyright Serge Rodovnichenko, 2015-2016
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
$wf_config = shopWorkflow::getConfig();
$wf_sdekint = include(dirname(dirname(dirname(__FILE__))) . '/config/workflow.php');
$is_config_changed = false; // чтоб не сохранять конфиг, если он не изменился

// Добавим действия из нашего конфига, если их еще нет в конфиге Магазина
foreach ($wf_sdekint['actions'] as $action_id => $action) {
    if (!array_key_exists($action_id, $wf_config['actions'])) {
        $is_config_changed = true;
        $wf_config['actions'][$action_id] = $action;

        // и разрешим добавленное действие в стандартных статусах
        foreach (array('new', 'processing', 'paid') as $state) {
            if (!in_array($action_id, $wf_config['states'][$state]['available_actions'])) {
                $wf_config['states'][$state]['available_actions'][] = $action_id;
            }
        }
    }
}

if ($is_config_changed) {
    if (!shopWorkflow::setConfig($wf_config)) {
        waLog::log('Sdekintplugin Update Error: cannot save changed workflow!');
    }
}
