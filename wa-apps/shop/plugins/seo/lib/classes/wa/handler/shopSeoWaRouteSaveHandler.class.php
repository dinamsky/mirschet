<?php

class shopSeoWaRouteSaveHandler
{
	public function handle($params, shopSeoWaRoutingMemento $routing_memento)
	{
		$is_saved_route_new = $params['is_new'];
		if ($is_saved_route_new) {
			return;
		}

		try {
			list($old_storefront, $new_storefront) = $this->tryDetermineStorefrontRename($params, $routing_memento);
		} catch (Exception $e) {
			return;
		}


		$group_category_storefront_model = new shopSeoGroupCategoryStorefrontModel();
		$group_storefront_model = new shopSeoGroupStorefrontModel();
		$group_storefront_storefront_model = new shopSeoGroupStorefrontStorefrontModel();


		$this->renameStorefront($old_storefront, $new_storefront, 'storefront', $group_category_storefront_model);
		$this->renameStorefront($old_storefront, $new_storefront, 'name', $group_storefront_model);
		$this->renameStorefront($old_storefront, $new_storefront, 'storefront', $group_storefront_storefront_model);
	}

	/**
	 * @param $params
	 * @param shopSeoWaRoutingMemento $routing_memento
	 * @return array
	 * @throws Exception
	 */
	private function tryDetermineStorefrontRename($params, shopSeoWaRoutingMemento $routing_memento)
	{
		$domain = $params['domain'];
		$route_id = $params['route_id'];

		$routing_before_save = $routing_memento->getStoredRouting();

		$original_route = ifset($routing_before_save, $domain, $route_id, null);
		$updated_route = $params['route'];

		$original_route_app = ifset($original_route, 'app', null);
		$updated_route_app = ifset($updated_route, 'app', null);

		$original_route_url = ifset($original_route, 'url', null);
		$updated_route_url = ifset($updated_route, 'url', null);

		if (
			$original_route_app === 'shop' && $updated_route_app === 'shop'
			&& is_string($original_route_url) && is_string($updated_route_url)
			&& $original_route_url !== $updated_route_url
		) {
			$old_storefront = "{$domain}/{$original_route_url}";
			$new_storefront = "{$domain}/{$updated_route_url}";

			return array($old_storefront, $new_storefront);
		}

		throw new Exception('Url маршрута магазина не изменен');
	}

	private function renameStorefront(
		$old_storefront,
		$new_storefront,
		$storefront_field,
		waModel $seo_model
	)
	{
		$with_new_storefront_count = $seo_model
			->select('COUNT(*)')
			->where("`{$storefront_field}` = :s", array('s' => $new_storefront))
			->fetchField();

		if (intval($with_new_storefront_count) === 0) {
			$seo_model->updateByField([
				$storefront_field => $old_storefront,
			], [
				$storefront_field => $new_storefront,
			]);
		}
	}
}