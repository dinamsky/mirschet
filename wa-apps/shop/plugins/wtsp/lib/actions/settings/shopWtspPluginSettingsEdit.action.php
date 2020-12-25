<?php
/**
*
*/
class shopWtspPluginSettingsEditAction extends waViewAction
{

	public function execute(){
		$id = (int) waRequest::request('id');

		if( empty($id) ) return;

		$model = new shopWtspPluginModel();
		$res = $model->getById($id);
		if($res){
			$this->view->assign('param', $res);
		}
	}
}
