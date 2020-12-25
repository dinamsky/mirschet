<?php

return array
	(
		'name' => 'Подарочный сертификат',
		'version' => '1.0',
		'vendor' => 995002,
		'description' => 'Автоматизированная продажа подарочных сертификатов',
		'img' => 'img/giftcard.png',
		'frontend' => true,
		'shop_settings' => true,
		'handlers' => array
						(
							'order_action.pay' => 'order_paid',
							'order_action.complete' => 'order_complete',
							'order_action.edit' => 'order_edit',
							'order_action.delete' => 'order_delete',
							'order_action.refund' => 'order_refund',
							'backend_order' => 'backend_order',
							'frontend_my_order' => 'frontend_my_order',
						),
	);