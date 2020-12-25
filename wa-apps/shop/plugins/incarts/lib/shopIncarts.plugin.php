<?php

class shopIncartsPlugin extends shopPlugin
{
	
	public function frontendHead()
	{
		$root = wa()->getRootUrl();

		$show_info = htmlspecialchars($this->getSettings('show_info'), ENT_QUOTES);
		$submit_name = htmlspecialchars($this->getSettings('submit_name'), ENT_QUOTES);
		$submit_class = htmlspecialchars($this->getSettings('submit_class'), ENT_QUOTES);

		/*
		$noInCarts = '';
		if ($submit_name) $noInCarts .= "$(this).val('" . $submit_name . "');";
		if ($submit_class) $noInCarts .= "$(this).addClass('" . $submit_class . "');";
		if ($show_info) $noInCarts .= "$(this).parents('form').find('.noInCarts').html('" . $show_info . "');";

		$inCarts = '';
		if ($submit_name) $inCarts .= "$('.inCarts').parents('form').find(\"input[type='submit']\").val('" . $submit_name . "');";
		if ($submit_class) $inCarts .= "$('.inCarts').parents('form').find(\"input[type='submit']\").addClass('" . $submit_class . "');";
		if ($show_info) $inCarts .= "$('.inCarts').html('" . $show_info . "');";
		*/

		$html = "<script>
			if (typeof(jQuery) == 'undefined') {
				document.write('<script src=\"{$root}wa-apps/shop/plugins/incarts/js/jquery-3.1.0.min.js\" type=\"text/javascript\"><\/script>');
			}		

			jQuery( document ).ready(function( $ ) {
				if($('span').is('.noInCarts')) {
					if ($('.noInCarts').parents('form').find(\"button[type='submit'] span\").length) {
						$('.noInCarts').parents('form').find(\"button[type='submit']\").each(function(index, element) {
							$(element).on('click', function() {";

if ($submit_name) $html .= "$(this).find('span').html('" . $submit_name . "');";
if ($submit_class) $html .= "$(this).addClass('" . $submit_class . "');";
if ($show_info) $html .= "$(this).parents('form').find('.noInCarts').html('" . $show_info . "');";

							$html .= "})
						});
					} else {
						$('.noInCarts').parents('form').find(\"input[type='submit']\").each(function(index, element) {
							$(element).on('click', function() {";

if ($submit_name) $html .= "$(this).val('" . $submit_name . "');";
if ($submit_class) $html .= "$(this).addClass('" . $submit_class . "');";
if ($show_info) $html .= "$(this).parents('form').find('.noInCarts').html('" . $show_info . "');";

							$html .= "})
						});
					}

				}

				if($('span').is('.inCarts')) {
					if ($('.inCarts').parents('form').find(\"button[type='submit'] span\").length) {";

if ($submit_name) $html .= "$('.inCarts').parents('form').find(\"button[type='submit'] span\").html('" . $submit_name . "');";
if ($submit_class) $html .= "$('.inCarts').parents('form').find(\"button[type='submit']\").addClass('" . $submit_class . "');";
if ($show_info) $html .= "$('.inCarts').html('" . $show_info . "');";

					$html .= "} else {";

if ($submit_name) $html .= "$('.inCarts').parents('form').find(\"input[type='submit']\").val('" . $submit_name . "');";
if ($submit_class) $html .= "$('.inCarts').parents('form').find(\"input[type='submit']\").addClass('" . $submit_class . "');";
if ($show_info) $html .= "$('.inCarts').html('" . $show_info . "');";

					$html .= "}
				}
			});
		</script>";	

		return $html;
	}
	
    public static function showInfo($product_id)
    {
		if (isset($_COOKIE['shop_cart'])) {
			$shop_cart = $_COOKIE['shop_cart'];
			
			$model = new shopIncartsPluginCartModel();
			
			$record = $model->getByField(array('code' => $shop_cart, 'product_id' => $product_id));

			if (isset($record['quantity']) AND $record['quantity'] > 0) {
				return '<span class="inCarts"></span>';
			} else {
				return '<span class="noInCarts"></span>';
			}		
		}
		
		return '';
    }
	
}