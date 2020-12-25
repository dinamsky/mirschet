<?php

$model = new waModel();

$model->exec("
DELETE FROM shop_seoredirect_errors
WHERE url LIKE '%/ordercall-config/'
");
