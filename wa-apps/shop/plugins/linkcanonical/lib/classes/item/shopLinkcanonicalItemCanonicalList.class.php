<?php

class shopLinkcanonicalItemCanonicalList
{
	protected $list = array();

	/**
	 * shopLinkcanonicalItemCanonicalList constructor.
	 * @param array $array [shopLinkcanonicalIItemCanonical, ...]
	 */
	public function __construct($array = array())
	{
		$this->setList($array);
	}

	public function append(shopLinkcanonicalIItemCanonical $item)
	{
		$this->list[] = $item;
	}

	/**
	 * @param array $list [shopLinkcanonicalIItemCanonical, ...]
	 * @return shopLinkcanonicalItemCanonicalList
	 */
	public function setList($list)
	{
		if (!is_array($list))
		{
			return $this;
		}
		foreach ($list as $item)
		{
			if ($item instanceof shopLinkcanonicalIItemCanonical)
			{
				$this->append($item);
			}
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getList()
	{
		return $this->list;
	}
}