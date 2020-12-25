<?php
// В одном из обновлений проскочил дважды минифицированный файл. Issue /syrnik-webasyst/sdekint/#65
waFiles::delete(wa('shop')->getConfig()->getPluginPath('sdekint') . '/js/sugar.min.min.js');
