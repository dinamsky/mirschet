<?php
$sqls[] = array(
    'field' => 'country_name_sdek',
    'cmd'   => array(
        "ALTER TABLE shop_sdekint_pvz ADD country_name_sdek varchar(50) DEFAULT '' NOT NULL",
        "ALTER TABLE shop_sdekint_pvz MODIFY COLUMN country_name_sdek varchar(50) NOT NULL DEFAULT '' AFTER name"
    )
);

$sqls[] = array(
    'field' => 'country_code_sdek',
    'cmd'   => array(
        "ALTER TABLE shop_sdekint_pvz ADD country_code_sdek varchar(10) DEFAULT '' NOT NULL",
        "ALTER TABLE shop_sdekint_pvz MODIFY COLUMN country_code_sdek varchar(10) NOT NULL DEFAULT '' AFTER country_name_sdek"
    )
);

$sqls[] = array(
    'field' => 'region_code_sdek',
    'cmd'   => array(
        "ALTER TABLE shop_sdekint_pvz ADD region_code_sdek varchar(10) DEFAULT '' NOT NULL",
        "ALTER TABLE shop_sdekint_pvz MODIFY COLUMN region_code_sdek varchar(10) NOT NULL DEFAULT '' AFTER country_code_sdek"
    )
);

$sqls[] = array(
    'field' => 'full_address',
    'cmd'   => array(
        "ALTER TABLE shop_sdekint_pvz ADD full_address varchar(255) DEFAULT '' NULL",
        "ALTER TABLE shop_sdekint_pvz MODIFY COLUMN full_address varchar(255) DEFAULT '' AFTER address"
    )
);

$sqls[] = array(
    'field' => 'dressing_room',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD dressing_room int(1) DEFAULT 0 NOT NULL")
);

$sqls[] = array(
    'field' => 'have_cashless',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD have_cashless int(1) DEFAULT 0 NOT NULL")
);

$sqls[] = array(
    'field' => 'allowed_cod',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD allowed_cod int(1) DEFAULT 0 NOT NULL")
);

$sqls[] = array(
    'field' => 'nearest_station',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD nearest_station varchar(50) DEFAULT '' NOT NULL")
);

$sqls[] = array(
    'field' => 'metro',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD metro varchar(50) DEFAULT '' NOT NULL")
);

$sqls[] = array(
    'field' => 'region_name_sdek',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD region_name_sdek varchar(50) DEFAULT '' NOT NULL")
);

$sqls[] = array(
    'field' => 'site',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD site varchar(255) DEFAULT '' NOT NULL")
);

$sqls[] = array(
    'field' => 'address_comment',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD address_comment varchar(255) DEFAULT '' NOT NULL")
);

$sqls[] = array(
    'field' => 'raw_data',
    'cmd'   => array("ALTER TABLE shop_sdekint_pvz ADD raw_data TEXT DEFAULT NULL")
);

$m = new waModel();

foreach ($sqls as $field) {
    try {
        $result = $m->query("SELECT {$field['field']} FROM shop_sdekint_pvz WHERE 1=1 LIMIT 1");
    } catch (Exception $e) {
        foreach ($field['cmd'] as $cmd) {
            try {
                $m->exec($cmd);
            } catch (Exception $e) {
                waLog::log('SDEKint Update ' . __FILE__ . ' error: ' . $e->getMessage() . '. Command: ' . $cmd, 'sdekint.log');
                break;
            }
        }
    }
}
