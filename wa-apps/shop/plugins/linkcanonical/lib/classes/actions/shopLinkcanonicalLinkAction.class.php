<?php

class shopLinkcanonicalLinkAction
{
	/** @var shopLinkcanonicalILinkCanonical|null */
	private static $canonical_settings = null;

	public static function getCanonical(shopLinkcanonicalILinkCanonical $canonical, $full = false)
	{
		return $canonical->getCanonical($full);
	}

	public static function getLink($full = false)
	{
		/**
		 *  Персональный canonical для товаров
		 */
		$product = new shopLinkcanonicalCanonicalProduct();
		if ($product->isTag())
		{
			self::$canonical_settings = $product;

			return self::$canonical_settings->getCanonical($full);
		}

		/**
		 *  Персональный canonical для категорий
		 */
		$category = new shopLinkcanonicalCanonicalCategory();
		if ($category->isTag())
		{
			self::$canonical_settings = $category;

			return self::$canonical_settings->getCanonical($full);
		}

		/**
		 *  Canonical с учетом настроек
		 */
		self::$canonical_settings = new shopLinkcanonicalCanonicalSettings();
		return self::$canonical_settings->getCanonical($full);
	}

	public static function getLastLinkCanonicalSettings()
	{
		return self::$canonical_settings;
	}
}