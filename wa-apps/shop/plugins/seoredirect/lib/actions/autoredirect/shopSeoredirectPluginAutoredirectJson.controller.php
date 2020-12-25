<?php

class shopSeoredirectPluginAutoredirectJsonController extends waJsonController
{
	public function execute()
	{
		if (method_exists($this, waRequest::get('method')))
		{
			call_user_func(array($this, waRequest::get('method')));
		}
	}

	public function get()
	{
		$shop_urls_model = new shopSeoredirectShopUrlsModel();

		$q = waRequest::get('q', '');
		$type = waRequest::get('type');
		$order = waRequest::get('order', 'desc', 'string');
		$sort = waRequest::get('sort', 'create_datetime', 'string');
		$page = max(1, waRequest::get('page', 1));
		$count_on_page = max(1, waRequest::get('count', 20));
		$count_on_page = max(20, $count_on_page);

		$where = '';
		if (!is_null($type) && $type != '' && $type != 'all')
		{
			$where .= "type=" . $type;
		}
		else
		{
			$type = null;
		}
		if (!empty($q))
		{
			$search = empty($where) ? '' : $where . ' AND ';
			$q_array = explode('/', $q);
			foreach ($q_array as $key => $url)
			{
				if ($url == '')
				{
					unset($q_array[$key]);
					continue;
				}
				$q_array[$key] = $shop_urls_model->escape($url);
			}
			$search .= "url IN ('" . implode("', '", $q_array) . "')";
			$where = $search;

		}

		$count_all = $shop_urls_model->countWhere($where);

		$page = shopSeoredirectHelper::minPage($count_on_page, $page, $count_all);
		$limit = $count_on_page * ($page - 1) . ',' . $count_on_page;
		$autoredirects = $shop_urls_model->select('*')->order($sort . ' ' . $order)->limit($limit);

		if (!empty($where))
		{
			$autoredirects->where($where);
		}
		$autoredirects = $autoredirects->fetchAll();

		$types = $shop_urls_model->getDataTypes();
		$types_name = array();
		foreach ($types as $type)
		{
			$types_name[$type] = shopSeoredirectViewHelper::getNameByType($type);
		}

		foreach ($autoredirects as &$autoredirect)
		{
			$autoredirect['view'] = array(
				'type' => shopSeoredirectViewHelper::getNameByType($autoredirect['type']),
				'id' => shopSeoredirectViewHelper::getBackendUrlByData($autoredirect),
				'url' => shopSeoredirectViewHelper::truncate($autoredirect['url'], 25, '/.../', true, true),
				'full_url' => shopSeoredirectViewHelper::truncate($autoredirect['full_url'], 45, '/.../', true, true),
				'parent_id' => shopSeoredirectViewHelper::getParentBackendUrlByData($autoredirect),
				'create_datetime' => shopSeoredirectViewHelper::date('humandatetime', $autoredirect['create_datetime']),
			);
		}
		unset($autoredirect);

		$this->response = array(
			'autoredirects' => $autoredirects,
			'page' => $page,
			'count_on_page' => $count_on_page,
			'count_all' => $count_all,
			'types' => $types_name,
		);

	}

	public function delete()
	{
		$hash = waRequest::get('hash');
		$hashs = waRequest::post('hashs');

		$shop_urls_model = new shopSeoredirectShopUrlsModel();
		$shop_urls_model->delete($hash);
		$shop_urls_model->delete($hashs);
	}

	public function deleteAll()
	{
		$shop_urls_model = new shopSeoredirectShopUrlsModel();
		$shop_urls_model->query('TRUNCATE TABLE ' . $shop_urls_model->getTableName());
	}

	public function reinstall()
	{
		$url_archivator = new shopSeoredirectUrlArchivator();
		$url_archivator->run();
	}
}