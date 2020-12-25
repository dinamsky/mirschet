<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopQuickorderPluginBackendActions extends waJsonActions
{

    public function revertSkuStocksTmplAction()
    {
        $this->response = file_get_contents(wa()->getAppPath('plugins/quickorder/templates/actions/frontend/include.sku_variants.stocks.html', 'shop'));
    }
}
