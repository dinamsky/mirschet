<?php

/**
 * Class shopMetrikaPlugin
 */
class shopMetrikaPlugin extends shopPlugin {
	public function backendmenu() {
        $html = "
		<li><a href='#/metrika/'>Яндекс.Метрика</a></li>
		<script type='text/javascript' src='".$this->getPluginStaticUrl()."js/metrika.min.js'></script>
		<script type='text/javascript' src='".wa()->getRootUrl()."wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.canvasTextRenderer.min.js'></script>
		<script type='text/javascript' src='".wa()->getRootUrl()."wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.canvasAxisTickRenderer.min.js'></script>
		<script type='text/javascript' src='".wa()->getRootUrl()."wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.barRenderer.min.js'></script>
		<script type='text/javascript' src='".wa()->getRootUrl()."wa-content/js/jquery-plugins/jquery-plot/plugins/jqplot.categoryAxisRenderer.min.js'></script>

		<link href='".$this->getPluginStaticUrl()."css/metrika.css' rel='stylesheet' type='text/css' />
		<script>
		$(function(){
			$.reports.metrikaAction = function() {
				$('#reportscontent').load('?plugin=metrika', function() {
				    $('.metrika-menu li:first a').click();
				});
			}
		});
		</script>
		";
      return array(
            'menu_li' => $html,
        );
	}
}
?>