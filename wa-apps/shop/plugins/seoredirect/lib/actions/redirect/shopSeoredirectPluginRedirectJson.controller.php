<?php

class shopSeoredirectPluginRedirectJsonController extends waJsonController
{
	public function execute()
	{
		$method = waRequest::get('method');
		if (!is_string($method))
		{
			throw new waException('', 404);
		}

		$method = strtolower($method);

		if ($method == 'save')
		{
			$this->save();
		}
		elseif ($method === 'sort')
		{
			$this->sort();
		}
		elseif ($method === 'get')
		{
			$this->get();
		}
		elseif ($method === 'getredirect')
		{
			$this->getRedirect();
		}
		elseif ($method === 'delete')
		{
			$this->delete();
		}
		elseif ($method === 'saveredirect')
		{
			$this->saveRedirect();
		}
		elseif ($method === 'deleteallredirects')
		{
			$this->deleteAllRedirects();
		}
	}

	public function save()
	{
		$redirect = waRequest::post('redirect');

		if (!$redirect)
		{
			return;
		}

		$redirect_id = waRequest::post('edit');
		$redirect['id'] = $redirect_id;
		$redirect_model = new shopSeoredirectRedirectModel();
		$check_redirect = $redirect_model->getByField('url_from', $redirect['url_to']);
		if (!!$check_redirect)
		{
			$this->response = array('url_to' => $check_redirect['url_to']);
			return;
		}
		$redirect_model->addRedirect($redirect);

		$parent_error_id = waRequest::post('error');

		if ($parent_error_id)
		{
			$error_storage = new shopSeoredirectErrorStorage();
			$error_storage->deleteById($parent_error_id);
		}
	}

	public function sort()
	{
		$redirect_model = new shopSeoredirectRedirectModel();
		$data = waRequest::post('data', array());
		//$sort = waRequest::post('sort');

		if (count($data) && is_array($data))
		{
			$redirect_model->newSortingByIds($data);
			$this->response = $data;
		}
		else
		{
			$this->setError('Not data');
		}
	}

	public function get()
	{
		$redirect_model = new shopSeoredirectRedirectModel();

		$page = max(1, waRequest::get('page', 1));
		$sort = waRequest::get('sort', 'sort');
		$order = waRequest::get('order', 'asc');

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

		$order_query = $this->getOrderQuery($sort, $order);

		$count_all = $redirect_model->countAll();
		$page = shopSeoredirectHelper::minPage($count_on_page, $page, $count_all);
		$limit = $count_on_page * ($page - 1) . ',' . $count_on_page;
		$redirects = $redirect_model
			->select('*')
			->order($order_query)
			->limit($limit)
			->fetchAll();

		$code_http_text = array(301 => '301 - Перемещено навсегда', 302 => '302 - Временно перемещено');
		$domain = wa()->getRouting()->getDomain();
		foreach($redirects as $key => &$redirect)
		{
			// begin create view redirect
			$redirect['view'] = array();
			$redirect['view']['domain'] = $redirect['domain'] == 'general' ? 'Все домены': $redirect['domain'];
			$redirect['view']['code_http'] = $code_http_text[$redirect['code_http']];
			if ($redirect['domain'] != 'general')
			{
				$redirect['view']['url_domain'] = '//' . str_replace('*', '', $redirect['domain']);
			}
			else
			{
				$redirect['view']['url_domain'] = '//' . $domain;
			}

			$redirect['view']['url_from_type'] = shopSeoredirectRedirect::isReg($redirect['url_from']);
			$redirect['view']['domain_url_from'] = !$redirect['view']['url_from_type'] ?
				$redirect['view']['url_domain'] . $redirect['url_from'] : '';

			$redirect['view']['url_from'] = shopSeoredirectViewHelper::truncate($redirect['url_from'], 45, '/.../', true, true);


			$redirect['view']['url_to_type'] = shopSeoredirectRedirect::isReg($redirect['url_to']);
			if ($redirect['view']['url_to_type'])
			{
				$redirect['view']['domain_url_to'] = '';
			}
			else if (shopSeoredirectViewHelper::isURL($redirect['url_to']) === false)
			{
				$redirect['view']['domain_url_to'] = $redirect['view']['url_domain'] . $redirect['url_to'];
			}
			else
			{
				$redirect['view']['domain_url_to'] = $redirect['url_to'];
			}

			$redirect['view']['url_to'] = shopSeoredirectViewHelper::truncate($redirect['url_to'], 25, '/.../', true, true);

			$redirect['view']['comment'] = shopSeoredirectViewHelper::truncate(strip_tags($redirect['comment']), 25);

			$redirect['view']['edit_datetime'] = shopSeoredirectViewHelper::date('humandatetime', $redirect['edit_datetime']);
			// end create view redirect
		}
		unset($redirect);

		$this->response = array(
			'redirects' => $redirects,
			'page' => $page,
			'count_on_page' => $count_on_page,
			'count_all' => $count_all,
			'sorting' => array(
				'sort' => $sort,
				'order' => $order,
			),
		);

	}

	public function getRedirect()
	{
		$redirect_id = waRequest::get('redirect_id');

		$redirect_model = new shopSeoredirectRedirectModel();
		$redirect = $redirect_model->getById($redirect_id);

		$routing = new shopSeoredirectWaRouting();
		$domains = $routing->getDomains();

		$this->response = array(
			'redirect' => $redirect,
			'domains' => $domains,
		);
	}

	public function delete()
	{
		$redirect_id = waRequest::get('redirect_id');
		$redirect_ids = waRequest::post('redirect_ids');

		$redirect_model = new shopSeoredirectRedirectModel();
		$redirect_model->delete($redirect_id);
		$redirect_model->delete($redirect_ids);
	}

	public function saveRedirect()
	{
		$type = waRequest::post('type');
		$redirect = waRequest::post('redirect');

		$redirect_model = new shopSeoredirectRedirectModel();
		switch ($type)
		{
			case 'new':
				unset($redirect['id']);
				$redirect_model->addRedirect($redirect);
				break;
			case 'redirect':
				$redirect_model->addRedirect($redirect);
				break;
			case 'error':
				$parent_error_id = $redirect['id'];
				if ($parent_error_id)
				{
					$error_storage = new shopSeoredirectErrorStorage();
					$error_storage->deleteById($parent_error_id);
				}
				unset($redirect['id']);
				$redirect_model->addRedirect($redirect);
				break;
		}
	}

	private function deleteAllRedirects()
	{
		$redirect_model = new shopSeoredirectRedirectModel();

		$redirect_model->deleteAll();
	}

	private function getOrderQuery($sort, $order)
	{
		$sort = trim(strtolower($sort));
		$order = trim(strtolower($order));

		$sort_order = $order === 'desc' ? 'DESC' : 'ASC';

		if ($sort === 'domain')
		{
			return $sort_order === 'ASC'
				? "domain = 'general' DESC, domain ASC"
				: "domain = 'general' ASC, domain DESC";
		}

		$sort_column = null;
		if ($sort === 'sort')
		{
			$sort_column = 'sort';
			$sort_order = 'ASC';
		}
		elseif ($sort === 'redirect_from')
		{
			$sort_column = 'url_from';
		}
		elseif ($sort === 'redirect_to')
		{
			$sort_column = 'url_to';
		}
		elseif ($sort === 'code_http')
		{
			$sort_column = 'code_http';
		}
		elseif ($sort === 'status')
		{
			$sort_column = 'status';
		}
		elseif ($sort === 'edit_datetime')
		{
			$sort_column = 'edit_datetime';
		}

		return $sort_column === null
			? 'sort ASC'
			: "{$sort_column} {$sort_order}";
	}
}
