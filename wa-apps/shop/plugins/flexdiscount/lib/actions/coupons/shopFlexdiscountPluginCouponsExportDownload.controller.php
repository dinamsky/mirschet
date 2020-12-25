<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginCouponsExportDownloadController extends waController
{

    public function execute()
    {
        $filename = waRequest::get("file");

        if (!wa()->getUser()->isAdmin() && !wa()->getUser()->getRights("shop", "flexdiscount_rules")) {
            throw new waRightsException('Access denied');
        }

        $file = $filename == 'import_example.csv' ? wa()->getAppPath('plugins/flexdiscount/templates/import_example' . (wa()->getLocale() == 'en_US' ? '_en' : '') . '.csv', 'shop') : wa()->getTempPath('flexdiscount/csv/export/' . $filename);
        if (file_exists($file)) {
            waFiles::readFile($file, $filename);
        }
    }

}
