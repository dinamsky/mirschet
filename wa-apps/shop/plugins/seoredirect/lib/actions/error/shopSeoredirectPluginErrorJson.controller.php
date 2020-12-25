<?php

class shopSeoredirectPluginErrorJsonController extends waJsonController
{
	public function execute()
	{
		$method = waRequest::get('method');
		if (is_string($method))
		{
			$method = strtolower($method);
		}
		
		if ($method === 'get')
		{
			$this->get();
		}
		elseif ($method === 'geterrorbyid')
		{
			$this->getErrorById();
		}
		elseif ($method === 'delete')
		{
			$this->delete();
		}
		elseif ($method === 'exclude')
		{
			$this->exclude();
		}
		elseif ($method === 'deleteallerrors')
		{
			$this->deleteAllErrors();
		}
		else
		{
			$this->errors[] = 'Unknown method';
		}
	}

	public function get()
	{
		$order = waRequest::get('order', 'desc', 'string');
		$sort = waRequest::get('sort', 'edit_datetime', 'string');
		$page = max(1, waRequest::get('page', 1));

		$count_on_page = waRequest::get('count');
		if (!wa_is_int($count_on_page) || !($count_on_page > 0))
		{
			$count_on_page = wa()->getStorage()->get('shop/seoredirect/count_on_page');
		}

		if (!wa_is_int($count_on_page) || !($count_on_page > 0))
		{
			$count_on_page = 20;
		}

		if ($count_on_page < 200)
		{
			wa()->getStorage()->set('shop/seoredirect/count_on_page', $count_on_page);
		}

		$error_storage = new shopSeoredirectErrorStorage();
		$count_all = $error_storage->getCount();
		$page = shopSeoredirectHelper::minPage($count_on_page, $page, $count_all);
		$errors  = $error_storage->getPage($count_on_page, $page, $sort, $order);

		foreach ($errors as &$error)
		{
			$error['view'] = array();
			$error['view']['url_domain'] = '//' . str_replace('*', '', $error['domain']);
			$error['view']['url'] = shopSeoredirectViewHelper::truncate($error['url'], 45, '/.../', true, true);
			$error['view']['http_referer'] = shopSeoredirectViewHelper::truncate($error['http_referer'], 25, '/.../', true, true);
			$error['view']['edit_datetime'] = shopSeoredirectViewHelper::date('humandatetime', $error['edit_datetime']);
			$error['view']['create_datetime'] = shopSeoredirectViewHelper::date('humandatetime', $error['create_datetime']);
		}

		$this->response = array(
			'errors' => $errors,
			'page' => $page,
			'count_on_page' => $count_on_page,
			'count_all' => $count_all,
			'sorting' => array(
				'sort' => $sort,
				'order' => $order,
			),
		);

	}

	public function getErrorById()
	{
		$error_id = waRequest::get('error_id');

		$error_storage = new shopSeoredirectErrorStorage();
		$error = $error_storage->getById($error_id);

		$routing = new shopSeoredirectWaRouting();
		$domains = $routing->getDomains();

		$this->response = array(
			'error' => $error,
			'domains' => $domains,
		);
	}

	public function delete()
	{
		$error_id = waRequest::get('error_id');
		$error_storage = new shopSeoredirectErrorStorage();
		
		if ($error_id)
		{
			$error_storage->deleteById($error_id);
			
			return;
		}
		
		
		$error_ids = waRequest::post('error_ids');
		
		if (!is_array($error_ids) || count($error_ids) == 0)
		{
			return;
		}
		
		$error_storage->deleteByIds($error_ids);
	}
	
	public function exclude()
	{
		$ids = waRequest::post('ids');
		
		if (!is_array($ids) || count($ids) == 0)
		{
			return;
		}
		
		$error_storage = new shopSeoredirectErrorStorage();
		
		foreach ($ids as $id)
		{
			$error_storage->addExclude($id);
		}
	}

	private function deleteAllErrors()
	{
		$error_storage = new shopSeoredirectErrorStorage();

		$error_storage->clean();
	}
}
