<?php

abstract class shopCatalogreviewsFixedLayout implements shopCatalogreviewsILayout
{
	/** @var array */
	protected $fields;
	/** @var array */
	protected $template_layout_assoc;

	/**
	 * @param array $template_layout_assoc
	 */
	public function __construct($template_layout_assoc = null)
	{
		$this->fields = array_fill_keys($this->getFixedLayoutFields(), '');

		$this->template_layout_assoc = $this->prepareTemplateLayoutAssoc($template_layout_assoc);
	}

	public function __get($field)
	{
		return array_key_exists($field, $this->template_layout_assoc)
			? $this->template_layout_assoc[$field]
			: '';
	}

	public function getLayoutAssoc()
	{
		return $this->template_layout_assoc;
	}

	public function hasField($field_name)
	{
		return array_key_exists($field_name, $this->fields);
	}

	/** @return array */
	abstract protected function getFixedLayoutFields();

	protected function prepareTemplateLayoutAssoc($template_layout_assoc)
	{
		if (!is_array($template_layout_assoc))
		{
			return $this->fields;
		}

		$prepared = [];
		foreach ($this->fields as $field => $_)
		{
			$prepared[$field] = array_key_exists($field, $template_layout_assoc)
				? $template_layout_assoc[$field]
				: '';

			if (is_string($prepared[$field]))
			{
				$prepared[$field] = trim($prepared[$field]);
			}
		}

		return $prepared;
	}
}
