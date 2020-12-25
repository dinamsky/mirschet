<?php

class shopCatalogreviewsWaCustomVariablesSource implements shopCatalogreviewsCustomVariablesSource
{
	public function getVariables()
	{
		$custom_variables = wa('shop')->event(['shop', 'seo_fetch_template_helper']);

		$variable_groups = [];

		foreach ($custom_variables as $plugin_key => $variables)
		{
			if (substr($plugin_key, -7) !== '-plugin')
			{
				continue;
			}
			$plugin_id = substr($plugin_key, 0, -7);

			$info = wa('shop')->getConfig()->getPluginInfo($plugin_id);

			if (is_array($info) && isset($info['name']))
			{
				$variable_groups[] = [
					'title' => $info['name'],
					'variables' => $this->workupVariables($variables),
				];
			}
		}

		return $variable_groups;
	}

	private function workupVariables($variables)
	{
		$result = [];
		foreach ($variables as $name => $description)
		{
			$result[] = [
				'variable' => $name,
				'description' => $description,
			];
		}

		return $result;
	}
}
