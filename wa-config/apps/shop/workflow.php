<?php
return array (
  'states' => 
  array (
    'new' => 
    array (
      'name' => 'Новый',
      'options' => 
      array (
        'icon' => 'icon16 ss new',
        'style' => 
        array (
          'color' => '#009900',
          'font-weight' => 'bold',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'process',
        1 => 'pay',
        2 => 'ship',
        3 => 'complete',
        4 => 'comment',
        5 => 'edit',
        6 => 'editcode',
        7 => 'editshippingdetails',
        8 => 'message',
        9 => 'delete',
        10 => 'sdek-send',
        11 => 'sdek-dismiss',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'processing' => 
    array (
      'name' => 'Подтвержден',
      'options' => 
      array (
        'icon' => 'icon16 ss confirmed',
        'style' => 
        array (
          'color' => '#008800',
          'font-style' => 'italic',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'pay',
        1 => 'ship',
        2 => 'complete',
        3 => 'comment',
        4 => 'edit',
        5 => 'editcode',
        6 => 'editshippingdetails',
        7 => 'message',
        8 => 'delete',
        9 => 'sdek-send',
        10 => 'sdek-dismiss',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'auth' => 
    array (
      'name' => 'Средства заблокированы',
      'options' => 
      array (
        'icon' => 'icon16 ss flag-white',
        'style' => 
        array (
          'color' => '#008800',
          'font-style' => 'italic',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'capture',
        1 => 'cancel',
        2 => 'comment',
        3 => 'message',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'paid' => 
    array (
      'name' => 'Оплачен',
      'options' => 
      array (
        'icon' => 'icon16 ss flag-yellow',
        'style' => 
        array (
          'color' => '#FF9900',
          'font-weight' => 'bold',
          'font-style' => 'italic',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'ship',
        1 => 'editcode',
        2 => 'editshippingdetails',
        3 => 'complete',
        4 => 'refund',
        5 => 'comment',
        6 => 'message',
        7 => 'sdek-send',
        8 => 'sdek-dismiss',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'shipped' => 
    array (
      'name' => 'Отправлен',
      'options' => 
      array (
        'icon' => 'icon16 ss sent',
        'style' => 
        array (
          'color' => '#0000FF',
          'font-style' => 'italic',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'editcode',
        1 => 'editshippingdetails',
        2 => 'complete',
        3 => 'comment',
        4 => 'delete',
        5 => 'message',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'completed' => 
    array (
      'name' => 'Выполнен',
      'options' => 
      array (
        'icon' => 'icon16 ss completed',
        'style' => 
        array (
          'color' => '#800080',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'editcode',
        1 => 'comment',
        2 => 'refund',
        3 => 'message',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'refunded' => 
    array (
      'name' => 'Возврат',
      'options' => 
      array (
        'icon' => 'icon16 ss refunded',
        'style' => 
        array (
          'color' => '#cc0000',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'message',
      ),
      'classname' => 'shopWorkflowState',
    ),
    'deleted' => 
    array (
      'name' => 'Удален',
      'options' => 
      array (
        'icon' => 'icon16 ss trash',
        'style' => 
        array (
          'color' => '#aaaaaa',
        ),
      ),
      'available_actions' => 
      array (
        0 => 'restore',
        1 => 'message',
      ),
      'classname' => 'shopWorkflowState',
    ),
  ),
  'actions' => 
  array (
    'create' => 
    array (
      'classname' => 'shopWorkflowCreateAction',
      'internal' => true,
      'name' => 'Создать',
      'options' => 
      array (
        'log_record' => 'Заказ оформлен',
      ),
      'state' => 'new',
    ),
    'process' => 
    array (
      'classname' => 'shopWorkflowProcessAction',
      'name' => 'В обработку',
      'options' => 
      array (
        'log_record' => 'Заказ подтвержден и принят в обработку',
        'button_class' => 'green',
        'description' => 'Статус заказа изменится на «В обработке».',
      ),
      'state' => 'processing',
    ),
    'pay' => 
    array (
      'classname' => 'shopWorkflowPayAction',
      'name' => 'Оплачен',
      'options' => 
      array (
        'log_record' => 'Заказ оплачен',
        'button_class' => 'yellow',
        'description' => 'Статус заказа изменится на «Оплачен». Сохранится дата оплаты.',
      ),
      'state' => 'paid',
    ),
    'ship' => 
    array (
      'classname' => 'shopWorkflowShipAction',
      'name' => 'Отправлен',
      'options' => 
      array (
        'log_record' => 'Заказ отправлен',
        'button_class' => 'blue',
        'description' => 'Статус заказа изменится на «Отправлен».',
      ),
      'state' => 'shipped',
    ),
    'refund' => 
    array (
      'classname' => 'shopWorkflowRefundAction',
      'name' => 'Возврат',
      'options' => 
      array (
        'log_record' => 'Возврат',
        'button_class' => 'red',
        'description' => 'Статус заказа изменится на «Возврат». Количество на складе для заказанных товаров и их артикулов, если непустое, увеличится соответственно. Дата оплаты будет удалена.',
      ),
      'state' => 'refunded',
    ),
    'edit' => 
    array (
      'classname' => 'shopWorkflowEditAction',
      'name' => 'Редактировать заказ',
      'options' => 
      array (
        'position' => 'top',
        'icon' => 'edit',
        'log_record' => 'Заказ отредактирован',
      ),
    ),
    'editcode' => 
    array (
      'classname' => 'shopWorkflowEditcodeAction',
      'name' => 'Изменить товарные коды',
      'options' => 
      array (
        'position' => 'top',
        'icon' => 'ss parameter',
        'log_record' => 'Изменены товарные коды',
      ),
    ),
    'editshippingdetails' => 
    array (
      'classname' => 'shopWorkflowEditshippingdetailsAction',
      'name' => 'Изменить параметры доставки',
      'options' => 
      array (
        'position' => 'top',
        'icon' => 'clock',
        'log_record' => 'Изменены параметры доставки',
      ),
    ),
    'delete' => 
    array (
      'classname' => 'shopWorkflowDeleteAction',
      'name' => 'Удалить',
      'options' => 
      array (
        'log_record' => 'Заказ удален',
        'description' => 'Статус заказа изменится на «Удален». Количество на складе для заказанных товаров и их артикулов, если непустое, увеличится соответственно. Дата оплаты будет удалена.',
      ),
      'state' => 'deleted',
    ),
    'restore' => 
    array (
      'classname' => 'shopWorkflowRestoreAction',
      'name' => 'Восстановить',
      'options' => 
      array (
        'icon' => 'restore',
        'log_record' => 'Заказ восстановлен',
        'button_class' => 'green',
        'description' => 'Статус заказа изменится на тот, который был у заказа до удаления.',
      ),
    ),
    'complete' => 
    array (
      'classname' => 'shopWorkflowCompleteAction',
      'name' => 'Выполнен',
      'options' => 
      array (
        'log_record' => 'Заказ выполнен',
        'button_class' => 'purple',
        'description' => 'Статус заказа изменится на «Выполнен». Сохранится дата оплаты.',
      ),
      'state' => 'completed',
    ),
    'message' => 
    array (
      'classname' => 'shopWorkflowMessageAction',
      'name' => 'Написать клиенту',
      'options' => 
      array (
        'position' => 'top',
        'icon' => 'email',
        'log_record' => 'Сообщение отправлено',
      ),
    ),
    'comment' => 
    array (
      'classname' => 'shopWorkflowCommentAction',
      'name' => 'Добавить комментарий',
      'options' => 
      array (
        'position' => 'bottom',
        'icon' => 'add',
        'button_class' => 'inline-link',
        'log_record' => 'Добавлен комментарий к заказу',
      ),
    ),
    'callback' => 
    array (
      'classname' => 'shopWorkflowCallbackAction',
      'internal' => true,
      'name' => 'Ответ платежной системы (callback)',
      'options' => 
      array (
        'log_record' => 'Ответ платежной системы (callback)',
      ),
    ),
    'auth' => 
    array (
      'classname' => 'shopWorkflowAuthAction',
      'internal' => true,
      'name' => 'Авторизовать платеж',
      'state' => 'auth',
      'options' => 
      array (
        'log_record' => 'Платеж авторизован',
      ),
    ),
    'settle' => 
    array (
      'classname' => 'shopWorkflowSettleAction',
      'internal' => true,
      'name' => 'Объединить',
      'options' => 
      array (
        'log_record' => 'Заказ был объединен с другим',
      ),
    ),
    'cancel' => 
    array (
      'classname' => 'shopWorkflowCancelAction',
      'internal' => true,
      'name' => 'Отменить платеж',
      'state' => 'refunded',
      'options' => 
      array (
        'button_class' => 'red',
        'log_record' => 'Оплата заказа отменена',
      ),
    ),
    'capture' => 
    array (
      'classname' => 'shopWorkflowCaptureAction',
      'internal' => true,
      'state' => 'paid',
      'name' => 'Списать средства',
      'options' => 
      array (
        'button_class' => 'red',
        'log_record' => 'Средства списаны для оплаты заказа',
      ),
    ),
    'sdek-send' => 
    array (
      'name' => 'В СДЭК',
      'options' => 
      array (
        'log_record' => 'Оформлен заказ СДЭК',
        'icon' => 'ss shipping-bw',
        'position' => '',
        'button_class' => '',
        'border_color' => '1d780a',
      ),
      'id' => 'sdek-send',
      'classname' => 'shopSdekintPluginSdekSendAction',
    ),
    'sdek-dismiss' => 
    array (
      'name' => 'Отозвать из СДЭК',
      'id' => 'sdek-dismiss',
      'classname' => 'shopSdekintPluginSdekDismissAction',
      'options' => 
      array (
        'log_record' => 'Заказ отзван из СДЭК',
        'icon' => 'cross',
        'position' => 'top',
      ),
    ),
  ),
);
