<?php

class shopFacebookproAddVariationIDorArticulNumber
{
private $productIntID; //ID товарв
private $variationProductID; //ID Вариации
private $productid1c; //1C номер карточки товара
private $variationit1c; //1C номер у вариации
private $kodArticOrID_id_good; //Если 1 то код артикула, если null то ид вариации ID из базы данных
private $use1C_ms1c_goods; //Если 1 то чекбокс 1С или Мой Склад.
private $kodNow; //Значение g:id вариации в даный момент

//	public function expensiveId() { //Шифрование на Вебавсист платформе запрещено правилами администрации!!!
//		$expensivesearch = convert_uudecode('C)B,Q,#@U.R8C,3`X-CLF(S$P-S0[)B,Q,#DY.R8C,3`X,3L`');
//		return $expensivesearch;
//	}
//	public function stopthief(){
//		$stopthief = convert_uudecode('+9SIC;VYD:71I;VX`\n`\n');
//		return $stopthief;
//	}

public function idOrArtVariation($productIntID, $variationProductID, $productid1c, $variationid1c, $kodArticOrID_id_good, $use1C_ms1c_goods, $kodNow) {
	$this->productIntID = $productIntID;
	$this->variationProductID = $variationProductID;
	$this->productid1c = $productid1c;
	$this->variationit1c = $variationid1c;
	$this->kodArticOrID_id_good = $kodArticOrID_id_good;
	$this->use1C_ms1c_goods = $use1C_ms1c_goods;
	$this->kodNow = $kodNow;

	//$sku_model = new shopProductSkusModel();
	//$skus = $sku_model->getDataByProductId($productIntID); // $skus[$variationProductID][id_1c] после отработки даного метода получаем в этом массиве номер 1С Вариации

		$container = new shopFacebookproCheck1CContainer($productid1c, $variationid1c);

		if ($kodArticOrID_id_good == null && $use1C_ms1c_goods == null or $kodArticOrID_id_good == null && $use1C_ms1c_goods == 1) {
			$kodNow = $productIntID;
			shopFacebookproPluginRunController::$itemgroupid = $productIntID;
			if(empty(shopFacebookproPluginRunController::$count_variation) or shopFacebookproPluginRunController::$count_variation == null) {
				shopFacebookproPluginRunController::$utm_link_variation ='&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = 'sku='.$variationProductID.'&';
				if (shopFacebookproPluginRunController::$count_variation_interation == 0) {shopFacebookproPluginRunController::$count_variation = null;} else {shopFacebookproPluginRunController::$count_variation = 1;}
			} elseif (shopFacebookproPluginRunController::$count_variation_interation == shopFacebookproPluginRunController::$count_variation ){
				$kodNow = $productIntID.'-'.$variationProductID;
				shopFacebookproPluginRunController::$utm_link_variation ='&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = 'sku='.$variationProductID.'&';
				shopFacebookproPluginRunController::$count_variation = null;
			}
			elseif(shopFacebookproPluginRunController::$count_variation >= 1) {
				$kodNow = $productIntID.'-'.$variationProductID;
				shopFacebookproPluginRunController::$utm_link_variation ='&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = 'sku='.$variationProductID.'&';
				shopFacebookproPluginRunController::$count_variation++;
			}
		}
		elseif ( $kodArticOrID_id_good == 1 && $use1C_ms1c_goods == null or $kodArticOrID_id_good == 1 && $use1C_ms1c_goods == 1) {
			$kodNow;
			 if ($kodNow == '') {
			 	$kodNow = $productIntID;
			 }
			shopFacebookproPluginRunController::$itemgroupid = $kodNow;
			if(empty(shopFacebookproPluginRunController::$count_variation) or shopFacebookproPluginRunController::$count_variation == null) {
				shopFacebookproPluginRunController::$utm_link_variation = '&utm_idbase='.$productIntID.'-'.$variationProductID.'&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = 'sku='.$variationProductID.'&';
				if (shopFacebookproPluginRunController::$count_variation_interation == 0) {shopFacebookproPluginRunController::$count_variation = null;} else {shopFacebookproPluginRunController::$count_variation = 1;}
			} elseif (shopFacebookproPluginRunController::$count_variation_interation == shopFacebookproPluginRunController::$count_variation ){
				$count = ++shopFacebookproPluginRunController::$count_variation;
				$kodNow = $kodNow . '-' . $count;
				shopFacebookproPluginRunController::$utm_link_variation = '&utm_idbase='.$productIntID.'-'.$variationProductID.'&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = '&sku='.$variationProductID.'&';
				shopFacebookproPluginRunController::$count_variation = null;
			}
			elseif(shopFacebookproPluginRunController::$count_variation >= 1) {
				$count = ++shopFacebookproPluginRunController::$count_variation;
				$kodNow = $kodNow . '-' . $count;
				shopFacebookproPluginRunController::$utm_link_variation = '&utm_idbase='.$productIntID.'-'.$variationProductID.'&utm_id='.$kodNow;
				shopFacebookproPluginRunController::$skulink = '&sku='.$variationProductID.'&';
			}
		}

	return $kodNow;
	}
}