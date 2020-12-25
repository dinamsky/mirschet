<?php
$model = new waModel();
$where = 'SELECT id FROM shop_vkgoods_wait_category WHERE 1';
try {
    $model->query($where);
} catch (Exception $e) {
    $query = 'ALTER TABLE shop_vkgoods_wait_category DROP PRIMARY KEY';
    $model->query($query);
    $query = 'ALTER TABLE shop_vkgoods_wait_category ADD id INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id)';
    $model->query($query);
}
