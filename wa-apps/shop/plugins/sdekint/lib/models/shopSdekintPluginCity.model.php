<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 3.2.0
 * @copyright Serge Rodovnichenko, 2015-2017
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
class shopSdekintPluginCityModel extends waModel
{
    protected $table = 'shop_sdekint_cities';

    /**
     * Ищет город по коду СДЭК
     *
     * @param string $code
     * @return array
     * @throws waException
     */
    public function getCityByCode($code)
    {
        $city = $this->getByField('sdek_id', $code);
        if(empty($city)) {
            return [];
        }

        $city['code'] = $code;
        $city['Country'] = $city['country_iso3'] ? (new waCountryModel)->get($city['country_iso3']) : [];
        $city['Region'] = $city['country_iso3'] && $city['region_code'] ? (new waRegionModel)->get($city['country_iso3'], $city['region_code']) : [];

        return $city;
    }

    /**
     * @param null|string $country_iso3
     * @param null|string $region_code
     * @param string $name
     * @return array
     * @throws waException
     */
    public function findByCountryRegionName($country_iso3 = null, $region_code = null, $name = '')
    {
        foreach ([$country_iso3, $name] as $param) {
            if (empty($param)) {
                return array();
            }
        }

        if (($country_iso3 == 'rus') && empty($region_code)) {
            return array();
        }

        $conditions = ['country_iso3' => $country_iso3, 'name' => $name];
        if ($region_code) {
            $conditions['region_code'] = $region_code;
        }

        return (array)$this->getByField($conditions, true);
    }
}