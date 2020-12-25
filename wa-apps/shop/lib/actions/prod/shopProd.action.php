<?php
/**
 * /products/<id>/
 * Redirects to default tab.
 */
class shopProdAction extends waViewAction
{
    public function execute()
    {
        $product_id = waRequest::param('id', '', 'int');
        if (!$product_id) {
            throw new waException('Not found', 404);
        }

        $url = wa()->getConfig()->getRootUrl().wa()->getConfig()->getBackendUrl().'/shop/products/'.$product_id.'/general/';
        $query_string = waRequest::server('QUERY_STRING', '', 'string');
        if (strlen($query_string)) {
            $url .= '?'.$query_string;
        }

        $this->redirect($url);
    }
}
