<?php

class shopCatalogreviewsLayoutsCollection
{
	protected $base_layout;
	protected $heap = [];
	protected $logs = [];

	public function __construct(shopCatalogreviewsILayout $base_layout)
	{
		$this->base_layout = $base_layout;
	}

	// todo понять нужны ли приоритет и коммент
	public function push(shopCatalogreviewsILayout $layout, $priority = 0, $comment = '')
	{
		$this->logs[] = [
			'layout' => $layout,
			'priority' => $priority,
			'comment' => $comment,
		];

		foreach ($layout->getLayoutAssoc() as $field => $value)
		{
			if (!$this->base_layout->hasField($field))
			{
				continue;
			}

			if ($this->isValueEmpty($value))
			{
				continue;
			}

			if (array_key_exists($field, $this->heap) && $priority <= $this->heap[$field]['priority'] + 1)
			{
				continue;
			}

			$this->heap[$field] = [
				'value' => $value,
				'priority' => $priority,
				'comment' => $comment,
			];
		}
	}

	/**
	 * @return shopCatalogreviewsILayout
	 */
	public function getResult()
	{
		$layout_assoc = [];

		foreach ($this->heap as $name => $data)
		{
			$layout_assoc[$name] = $data['value'];
		}

		return new $this->base_layout($layout_assoc);
	}

	public function getInfo()
	{
		return [
			'logs' => $this->logs,
			'heap' => $this->heap,
		];
	}

	protected function isValueEmpty($template)
	{
		return (is_string($template) && trim(strip_tags($template, '<img>')) === '') || $template === null;
	}
}
