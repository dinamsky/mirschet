<?php

class shopCatalogreviewsWaViewHelper
{
	public function getSortUrl($sort, $default_order, $name, $active_sort, $active_order)
	{
		$sort_url_params = waRequest::get();

		if ($sort)
		{
			$sort_url_params['sort'] = $sort;

			if ($sort == $active_sort)
			{
				$sort_url_params['order'] = $active_order == 'asc' ? 'desc' : 'asc';
			}
			else
			{
				$sort_url_params['order'] = $default_order;
			}
		}
		else
		{
			unset($sort_url_params['sort']);
			unset($sort_url_params['order']);
		}

		$html = '<a href="?' . http_build_query($sort_url_params) . '">'
			. $name
			. ($sort && $sort == $active_sort ? ' <i class="sort-' . ($sort_url_params['order'] == 'asc' ? 'desc' : 'asc') . '"></i>' : '')
			. '</a>';

		return $html;
	}
}
