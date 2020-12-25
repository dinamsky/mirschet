<?php


class shopSeoredirectErrorStorage
{
	private $errors_model;
	private $errors_exclude_model;
	
	public function __construct()
	{
		$this->errors_model = new shopSeoredirectErrorsModel();
		$this->errors_exclude_model = new shopSeoredirectErrorsExcludeModel();
	}
	
	public function getAll()
	{
		return $this->getAllIterable()->fetchAll();
	}
	
	public function getAllIterable()
	{
		return $this->errors_model->query("
			select e.*
			from shop_seoredirect_errors e
			left join shop_seoredirect_errors_exclude ee
				on e.id = ee.error_id
			where ee.error_id is null
		");
	}
	
	public function getPage($count_on_page, $page, $sort, $order)
	{
		$sort = $this->errors_model->escape($sort);
		$order = $this->errors_model->escape($order);
		$offset = intval($count_on_page * ($page - 1));
		$limit = intval($count_on_page);
		
		return $this->errors_model->query("
		select e.*
		from shop_seoredirect_errors e
		left join shop_seoredirect_errors_exclude ee
			on e.id = ee.error_id
		where ee.error_id is null
		order by {$sort} {$order}
		limit {$offset}, {$limit}
		")->fetchAll();
	}
	
	public function getById($id)
	{
		return $this->errors_model->getById($id);
	}
	
	public function getCount()
	{
		return $this->errors_model->query("
		select count(*) cnt
		from shop_seoredirect_errors e
		left join shop_seoredirect_errors_exclude ee
			on e.id = ee.error_id
		where ee.error_id is null
		")->fetchField();
	}
	
	public function addError($domain, $url, $error_code)
	{
		$this->errors_model->addError($domain, $url, $error_code);
	}
	
	public function deleteById($id)
	{
		$this->errors_model->deleteById($id);
	}
	
	public function deleteByIds($ids)
	{
		$this->errors_model->deleteByField('id', $ids);
	}
	
	public function addExclude($id)
	{
		$this->errors_exclude_model->replace(array(
			'error_id' => $id
		));
	}
	
	public function clean()
	{
		$this->errors_model->truncate();
		$this->errors_exclude_model->truncate();
	}
}
