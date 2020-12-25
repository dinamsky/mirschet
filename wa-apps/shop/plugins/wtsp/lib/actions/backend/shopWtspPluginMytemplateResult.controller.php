<?php

class shopWtspPluginMytemplateResultController extends waJsonController
{
    public function execute()
    {
        $order_id = (int) waRequest::request('order_id');
        $id = (int) waRequest::request('template_id');

        $model = new shopWtspPluginModel();
		$result = $model->getById($id);

        if(empty($result)) return;

        $om = new shopOrderModel();
        $o = $om->getById($order_id);
        if (!$o) {
            $this->errors = _w('Order not found');
            return;
        }

        $opm = new shopOrderParamsModel();
        $o['params'] = $opm->get($order_id, true);

        try {
            $contact = $o['contact_id'] ? new shopCustomer($o['contact_id']) : wa()->getUser();
            $contact->getName();
        } catch (Exception $e) {
            $contact = new shopCustomer(wa()->getUser()->getId());
        }

        $items_model = new shopOrderItemsModel();
        $o['items'] = $items_model->getItems($o['id']);
        foreach ($o['items'] as &$i) {
            if (!empty($i['file_name'])) {
                $i['download_link'] = wa()->getRouteUrl(
                    '/frontend/myOrderDownload',
                    array(
                        'id'   => $o['id'],
                        'code' => $o['params']['auth_code'],
                        'item' => $i['id'],
                    ),
                    true
                );
            }
        }

        $cm = new shopCustomerModel();
        $customer = $cm->getById($contact->getId());
        if (!$customer) {
            $customer = $cm->getEmptyRow();
        }

        $workflow = new shopWorkflow();

        $view = wa()->getView();
	    $view->assign( array(
            'order' => $o,
            'customer' => $contact,
            'status' => $workflow->getStateById($o['state_id'])->getName(),
            'order_url' => self::getOrderUrl($o),
        ));
        $content = $view->fetch('string:'.$result['description']);
        $this->response = $content;
    }
    private static function getOrderUrl($o)
    {
        $storefront = ifset($o['params']['storefront'], '');
        if (!$storefront) {
            // not storefront - get first storefront
            $storefronts = shopHelper::getStorefronts();
            $storefront = (string)reset($storefronts);
        }

        $order_domain = shopHelper::getDomainByStorefront($storefront);
        $order_url = wa()->getRouteUrl('/frontend/myOrderByCode', array('id' => $o['id'], 'code' => $o['params']['auth_code']), true, $order_domain);
        return $order_url;
    }

}
