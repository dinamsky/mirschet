<?php
return array(
	'shop_giftcard_coupons' => array(
		'order_id' => array('int', 11, 'null' => 0),
		'coupon_id' => array('int', 11, 'null' => 0),
		':keys' => array(
			'PRIMARY' => array('order_id', 'coupon_id'),
		),
	),
	'shop_giftcard_save_state' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'order_id' => array('int', 11, 'null' => 0),
		'coupon_code' => array('varchar', 32, 'null' => 0),
		'coupon_used' => array('int', 11, 'null' => 0),
		'coupon_value' => array('decimal', "15,4", 'null' => 0),
		'coupon_type' => array('varchar', 3, 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
		),
	),
);