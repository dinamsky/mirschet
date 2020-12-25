<?php

class shopCatalogreviewsReviewsCatalogTemplateVariablesMeta
{
	private $env;
	private $custom_variables_source;

	public function __construct(
		shopCatalogreviewsFrameworkEnv $wa_env,
		shopCatalogreviewsCustomVariablesSource $custom_variables_source
	)
	{
		$this->env = $wa_env;
		$this->custom_variables_source = $custom_variables_source;
	}

	public function getMeta()
	{
		return [
			'plugin_variables' => $this->getPluginVariables(),
			'modifiers' => $this->geModifiers(),
			'custom_variables' => $this->getCustomVariables(),
		];
	}

	private function getPluginVariables()
	{
		return [[
			'variable' => '{$category.name}',
			'description' => 'название категории',
		],[
			'variable' => '{$parent_category.name}',
			'description' => 'название родительской категории',
		],[
			'variable' => '{$parent_categories}',
			'description' => 'массив родительских категорий',
		],[
			'variable' => '{$parent_categories_names|sep:\' \'}',
			'description' => 'путь к категории через пробел',
		],[
			'variable' => '{$category.products_count}',
			'description' => 'количество товаров в категории',
		],[
			'variable' => '{$category.min_price}',
			'description' => 'минимальная цена в категории',
		],[
			'variable' => '{$pages_count}',
			'description' => 'количество страниц',
		],[
			'variable' => '{$page_number}',
			'description' => 'номер страницы',
		],[
			'variable' => '{$store_info.name}',
			'description' => 'название магазина',
		],[
			'variable' => '{$store_info.phone}',
			'description' => 'телефон магазина',
		],[
			'variable' => '{$host}',
			'description' => 'текущий домен',
		],];
	}

	private function geModifiers()
	{
		return [[
			'modifier' => '|lower',
			'description' => 'преобразует в нижний регистр',
		],[
			'modifier' => '|ucfirst',
			'description' => 'преобразует первый символ в верхний регистр',
		],[
			'modifier' => '|lcfirst',
			'description' => 'преобразует первый символ в нижний регистр',
		],[
			'modifier' => '|random',
			'description' => 'выбрать случайное значение из массива',
		],];
	}

	private function getCustomVariables()
	{
		$custom_variables = $this->custom_variables_source->getVariables();
		if ($this->env->isSeoPluginInstalled())
		{
			array_unshift($custom_variables, $this->getSeoVariablesGroup());
		}

		return $custom_variables;
	}

	// todo перенести в плагин seo
	private function getSeoVariablesGroup()
	{
		$seo_context = shopSeoContext::getInstance();

		$seo_variables = [];


		$seo_variables[] = [
			'variable' => '{$category.seo_name}',
			'description' => 'SEO-название категории',
		];

		foreach ($seo_context->getCategoryFieldService()->getFields() as $field)
		{
			$seo_variables[] = [
				'variable' => "{\$category.fields[{$field->getId()}].value}",
				'description' => $field->getName(),
			];
		}


		$seo_variables[] = [
			'variable' => '{$storefront.name}',
			'description' => 'название витрины',
		];

		foreach ($seo_context->getStorefrontFieldService()->getFields() as $field)
		{
			$seo_variables[] = [
				'variable' => "{\$storefront.fields[{$field->getId()}].value}",
				'description' => $field->getName(),
			];
		}


		return [
			'title' => 'SEO-оптимизация',
			'variables' => $seo_variables
		];
	}
}
