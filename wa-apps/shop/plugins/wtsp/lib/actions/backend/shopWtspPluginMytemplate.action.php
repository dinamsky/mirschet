<?php
/**
*
*/
class shopWtspPluginMytemplateAction extends waViewAction
{

	public function execute(){
		$model = new shopWtspPluginModel();

		$this->view->assign('list', $model->getAll() );

		$order_id = (int) waRequest::request('order_id');
		$this->view->assign('order_id', $order_id );
	}
}
