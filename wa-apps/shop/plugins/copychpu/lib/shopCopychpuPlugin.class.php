<?php

class shopCopychpuPlugin extends shopPlugin
{
    public function backendProductEdit()
    {

        $hook = $this->getSettings('hook');
        if ($hook == '1') {
            $watranslit = $this->getSettings('watranslit');
            $pluginurl = $this->getPluginStaticUrl();
            $plugin_version = waSystemConfig::isDebug() ? time() : ifset($this->info['version'], '1.0');
            //css, js
            $html = '<span id="copychpu__settings" data-watranslit="'.$watranslit.'" style="display: none"></span><link rel="stylesheet" href="'.$pluginurl.'css/copychpu.css?v'.$plugin_version.'" />
        <script src="'.$pluginurl.'js/copychpu.js?v'.$plugin_version.'"></script>
        ';
            return array('edit_section_li'=>$html);

        }
    }
}
