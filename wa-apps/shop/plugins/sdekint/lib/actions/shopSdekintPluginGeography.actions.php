<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginGeographyActions extends waJsonActions
{
    public function regionsAction()
    {
        $country_iso3 = waRequest::get('country', '', waRequest::TYPE_STRING_TRIM);
        if (empty($country_iso3)) {
            $this->response = [];
            return;
        }

        $this->response = array_values((array)(new waRegionModel)->getByCountry($country_iso3));
        return;
    }
}
