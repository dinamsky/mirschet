<?php

class shopCatalogreviewsPluginFrontendCategoryReviewsAction extends shopCatalogreviewsWaShopFrontendAction
{
	private $page;

	public function __construct($params = null)
	{
		parent::__construct($params);

		$page = waRequest::get('page', 1, waRequest::TYPE_INT);
		if ($page < 1)
		{
			$page = 1;
		}
		$this->page = $page;
	}

	protected function preExecute()
	{
		parent::preExecute();

		$config = $this->env->getConfig();
		if (!$config->plugin_is_enabled || !$config->category_is_enabled)
		{
			throw new waException('', 404);
		}
	}

	public function execute()
	{
		$category = $this->env->getCategory();

		$active_sort_mode = $this->env->getCurrentReviewsSortMode();
		list($active_sort, $active_order) = $this->getSorting($active_sort_mode);

		$this->setReviewsCollection($active_sort, $active_order);
		$this->assignReviewsplusVariables();
		$this->setSorting($active_sort, $active_order);
		$this->setMeta();

		$this->assignBreadcrumbs($category);

		$action_template = $this->action_template_storage->getCategoryReviewsTemplate();
		$this->setActionTemplate($action_template);

		$this->view->assign([
			'category' => $category,
			'config' => $this->env->getConfig(),
		]);
	}

	protected function setReviewsCollection($active_sort, $active_order)
	{
		$category = $this->env->getCategory();

		$reviews_collection = $this->context
			->getProductReviewsCollectionFactory()
			->buildCategoryReviewsCollection($category['id'], $this->env->getStorefront());

		//$reviews_collection->setWithReplies(true);
		$reviews_collection->setWithReplies(false);
		$reviews_collection->setIsPlainRepliesStructure(true);
		$reviews_collection->setPublishedOnly(true);
		$reviews_collection->setSort($active_sort, $active_order);

		// todo
		//$reviews_collection->filters(waRequest::get());

		$limit = $this->env->getConfig()->reviews_per_page;
		if (!$limit || $limit < 0 || $limit > 500)
		{
			$limit = 20;
		}
		$offset = ($this->page - 1) * $limit;

		if ($this->context->getFrameworkEnv()->isReviewsplusPluginEnabled())
		{
			$reviews_collection->addReviewsplusPluginFields(true);

			$reviews = $reviews_collection->getReviews($offset, $limit);
			$this->workupReviewsplusReviews($reviews);
		}
		else
		{
			$reviews = $reviews_collection->getReviews($offset, $limit);
		}

		$reviews_count = $reviews_collection->count();

		$pages_count = intval(ceil(($reviews_count - 1e-6) / $limit));

		$this->view->assign('pages_count', $pages_count);

		$this->view->assign('reviews', $reviews);
		$this->view->assign('reviews_count', $reviews_count);
	}

	private function setMeta()
	{
		$renderer = $this->context->getReviewsCatalogDataRenderer();

		$storefront = $this->env->getStorefront();
		$category = $this->env->getCategory();
		$config = $this->env->getConfig();

		$templates = [
			'h1' => $config->catalog_h1,
			'meta_title' => $config->catalog_meta_title,
			'meta_description' => $config->catalog_meta_description,
			'meta_keywords' => $config->catalog_meta_keywords,
			'description' => $config->catalog_description,
		];

		$rendered_templates = $renderer->renderAll(
			$storefront,
			$category['id'],
			$this->page,
			$templates
		);

		$this->view->assign([
			'h1' => $rendered_templates['h1'] ?: $category['name'],
			'description' => $rendered_templates['description'] ?: '',
		]);

		$response = $this->getResponse();

		if (trim($rendered_templates['meta_title']) !== '')
		{
			$response->setTitle($rendered_templates['meta_title']);
		}

		if (trim($rendered_templates['meta_description']) !== '')
		{
			$response->setMeta('description', $rendered_templates['meta_description']);
		}

		if (trim($rendered_templates['meta_keywords']) !== '')
		{
			$response->setMeta('keywords', $rendered_templates['meta_keywords']);
		}
	}

	private function assignBreadcrumbs($category)
	{
		$category_id = $category['id'];

		$breadcrumbs = [];

		$category_model = new shopCategoryModel();
		$path = array_reverse($category_model->getPath($category_id));
		$path[] = $category;

		foreach ($path as $row)
		{
			$breadcrumbs[] = [
				'url' => wa()->getRouteUrl('/frontend/category', ['category_url' => waRequest::param('url_type') == 1 ? $row['url'] : $row['full_url']]),
				'name' => $row['name'],
			];
		}

		$breadcrumbs[] = [
			'url' => $this->env->getPluginRouting()->getReviewsPageUrl($category),
			'name' => 'Отзывы',
		];

		$event_params = [
			'breadcrumbs' => $breadcrumbs,
			'override_current' => true,
		];
		wa('shop')->event('breadcrumbs_frontend_breadcrumbs.catalogreviews_categoryReviews', $event_params);

		if ($breadcrumbs)
		{
			$this->view->assign('breadcrumbs', $breadcrumbs);
		}
	}

	private function setSorting($active_sort, $active_order)
	{
		$config = $this->env->getConfig();

		$sort_options = [
			'rating' => [
				'name' => 'Оценка покупателей',
				'sort' => 'rating',
				'default_order' => 'desc',
			],
			'create_datetime' => [
				'name' => 'Дата добавления',
				'sort' => 'create_datetime',
				'default_order' => 'desc',
			],
		];

		$this->view->assign([
			'sort_options' => $sort_options,
			'active_sort' => $active_sort,
			'active_order' => $active_order,
			'is_client_sorting_enabled' => $config->catalog_is_client_sorting_enabled,
		]);
	}

	private function getSorting($sort_mode)
	{
		if ($sort_mode === shopCatalogreviewsReviewsSort::RATING_ASC)
		{
			return ['rating', 'asc'];
		}
		elseif ($sort_mode === shopCatalogreviewsReviewsSort::RATING_DESC)
		{
			return ['rating', 'desc'];
		}
		elseif ($sort_mode === shopCatalogreviewsReviewsSort::CREATE_DATETIME_ASC)
		{
			return ['create_datetime', 'asc'];
		}
		elseif ($sort_mode === shopCatalogreviewsReviewsSort::CREATE_DATETIME_DESC)
		{
			return ['create_datetime', 'desc'];
		}
		else
		{
			return ['create_datetime', 'desc'];
		}
	}

	private function assignReviewsplusVariables()
	{
		if (!$this->context->getFrameworkEnv()->isReviewsplusPluginEnabled())
		{
			return;
		}

		// todo refactor
		$fields_model = new shopReviewsplusPluginFieldsModel();
		$fields = $fields_model->select('*')->fetchAll('name_id');

		$this->view->assign('fields', $fields);
	}

	private function workupReviewsplusReviews(&$reviews)
	{
		// todo refactor
		$fields_model = new shopReviewsplusPluginFieldsModel();
		$reviewsplus_dop_model = new shopReviewsplusPluginDopModel();
		$fields = $fields_model->select('*')->fetchAll('name_id');

		$reviews_map = [];
		foreach ($reviews as $index => $review)
		{
			$reviews_map[$review['id']] = $index;
		}

		$dop_reviews = $reviewsplus_dop_model->getByField('review_id', array_keys($reviews_map), 'review_id');

		foreach ($dop_reviews as $review_id => $dop)
		{
			$index = $reviews_map[$review_id];

			foreach ($dop as $val => $key)
			{
				if (array_key_exists($val, $fields))
				{
					if ($fields[$val]['type'] === 'rate')
					{
						$reviews[$index]['dop_rate'][] = array(
							'name' => $fields[$val]['name'],
							'name_id' => $fields[$val]['name_id'],
							'value' => $key,
						);
					}
					else if ($fields[$val]['type'] === 'text')
					{
						$reviews[$index]['dop_text'][] = array(
							'name' => $fields[$val]['name'],
							'name_id' => $fields[$val]['name_id'],
							'value' => $key,
						);
					}
					else if ($fields[$val]['type'] === 'textarea')
					{
						$reviews[$index]['dop_textarea'][] = array(
							'name' => $fields[$val]['name'],
							'name_id' => $fields[$val]['name_id'],
							'value' => $key,
						);
					}
				}
			}
		}
	}
}
