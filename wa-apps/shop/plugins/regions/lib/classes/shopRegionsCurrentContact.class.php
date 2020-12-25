<?php

class shopRegionsCurrentContact
{
	private static $auth = null;

	/**
	 * @param shopRegionsCity $region_city
	 */
	public static function updateShipping($region_city)
	{
		if (!$region_city || waRequest::cookie('shop_region_remember_address', false))
		{
			return;
		}

		$contact = self::getCurrentContact();
		if (!($contact instanceof waContact))
		{
			return;
		}

		$address = $contact->get('address:shipping');//warm up the cache
		$add = count($address) === 0;

		$contact_city = $contact->get('address:city.shipping', 'default');
		$contact_region = $contact->get('address:region.shipping', 'default');
		$contact_country = $contact->get('address:country.shipping', 'default');

		if (self::getAuth())
		{
			if (empty($contact_city) && empty($contact_region) && empty($contact_country))
			{
				$city = $region_city->getName();
				$region = $region_city->getRegionCode();
				$country = $region_city->getCountryIso3();
			}
			else
			{
				return;
			}
		}
		else
		{
			$city = $region_city->getName();
			$region = $region_city->getRegionCode();
			$country = $region_city->getCountryIso3();

			if ($contact_city === $city && $contact_region === $region && $contact_country === $country)
			{
				return;
			}
		}

		$contact->set('address:country.shipping', $country, $add);
		$contact->set('address:region.shipping', $country && $region ? $region : '', $add);
		$contact->set('address:city.shipping', $country && $region && $city ? $city : '', $add);

		self::updateCurrentContact($contact);
	}

	public static function getCurrentContact()
	{
		$auth = self::getAuth();

		if ($auth)
		{
			$contact = new waContact($auth['id']);
		}
		else
		{
			$session = wa()->getStorage();
			$checkout = $session->get('shop/checkout');
			if (!$checkout)
			{
				$checkout = array();
			}

			$contact = isset($checkout['contact']) && ($checkout['contact'] instanceof waContact)
				? $checkout['contact']
				: new waContact();
		}

		return $contact;
	}

	/**
	 * @param waContact $contact
	 */
	private static function updateCurrentContact($contact)
	{
		if (!($contact instanceof waContact))
		{
			return;
		}

		$auth = self::getAuth();

		if ($auth)
		{
			$contact->save();
		}

		$session = wa()->getStorage();
		$checkout = $session->get('shop/checkout');
		if (!is_array($checkout))
		{
			$checkout = array();
		}
		$checkout['contact'] = $contact;


		if (!array_key_exists('order', $checkout))
		{
			$checkout['order'] = array();
		}

		if (!array_key_exists('region', $checkout['order']))
		{
			$checkout['order']['region'] = array();
		}

		$checkout['order']['region']['country'] = $contact->get('address:country.shipping', 'default');
		$checkout['order']['region']['region'] = $contact->get('address:region.shipping', 'default');
		$checkout['order']['region']['city'] = $contact->get('address:city.shipping', 'default');

		$session->set('shop/checkout', $checkout);
	}

	private static function getAuth()
	{
		if (self::$auth === null)
		{
			$is_template = waConfig::get('is_template');
			waConfig::set('is_template', false);

			self::$auth = wa()->getAuth()->isAuth();

			waConfig::set('is_template', $is_template);
		}

		return self::$auth;
	}
}