<?php


class shopRegionsIpAnalyzer
{
	public function analyze($ip)
	{
		include_once shopRegionsPlugin::getPath() . '/lib/vendor/sxgeo/shopRegionsSxGeo.class.php';
		$sessionStorage = wa()->getStorage();

		try
		{
			$sx_geo = new shopRegionsSxGeo(shopRegionsPlugin::getPath() . '/lib/vendor/sxgeo/SxGeo.dat');
			$data = $sx_geo->getCityFull($ip);

			$region_code = ifset(self::$iso_to_region_code[ifset($data['region']['iso'], '')]);
			if ($region_code)
			{
				$data['region']['code'] = $region_code;
			}
		}
		catch (Exception $e)
		{
			$data = array(
				'error' => $e->getMessage(),
			);
		}

		if (!ifset($data['error'], true))
		{
			$shop_regions_ipa_result = $sessionStorage->get('shop_regions_ipa_result');
			$shop_regions_ipa_result[$ip] = json_encode($data);
			$sessionStorage->set('shop_regions_ipa_result', $shop_regions_ipa_result);
		}

		return new shopRegionsIpAnalyzerResult($data);
	}

	private static $iso_to_region_code = array(
		'RU-AD' => '01',
		'RU-AL' => '04',
		'RU-BA' => '02',
		'RU-BU' => '03',
		'RU-DA' => '05',
		'RU-IN' => '06',
		'RU-KB' => '07',
		'RU-KL' => '08',
		'RU-KC' => '09',
		'RU-KR' => '10',
		'RU-KO' => '11',
		'RU-CR' => '91',
		'RU-ME' => '12',
		'RU-MO' => '13',
		'RU-SA' => '14',
		'RU-SE' => '15',
		'RU-TA' => '16',
		'RU-TY' => '17',
		'RU-UD' => '18',
		'RU-KK' => '19',
		'RU-CE' => '20',
		'RU-CU' => '21',
		'RU-ALT' => '22',
		'RU-ZAB' => '75',
		'RU-KAM' => '41',
		'RU-KDA' => '23',
		'RU-KYA' => '24',
		'RU-PER' => '59',
		'RU-PRI' => '25',
		'RU-STA' => '26',
		'RU-KHA' => '27',
		'RU-AMU' => '28',
		'RU-ARK' => '29',
		'RU-AST' => '30',
		'RU-BEL' => '31',
		'RU-BRY' => '32',
		'RU-VLA' => '33',
		'RU-VGG' => '34',
		'RU-VLG' => '35',
		'RU-VOR' => '36',
		'RU-IVA' => '37',
		'RU-IRK' => '38',
		'RU-KGD' => '39',
		'RU-KLU' => '40',
		'RU-KEM' => '42',
		'RU-KIR' => '43',
		'RU-KOS' => '44',
		'RU-KGN' => '45',
		'RU-KRS' => '46',
		'RU-LEN' => '47',
		'RU-LIP' => '48',
		'RU-MAG' => '49',
		'RU-MOS' => '50',
		'RU-MUR' => '51',
		'RU-NIZ' => '52',
		'RU-NGR' => '53',
		'RU-NVS' => '54',
		'RU-OMS' => '55',
		'RU-ORE' => '56',
		'RU-ORL' => '57',
		'RU-PNZ' => '58',
		'RU-PSK' => '60',
		'RU-ROS' => '61',
		'RU-RYA' => '62',
		'RU-SAM' => '63',
		'RU-SAR' => '64',
		'RU-SAK' => '65',
		'RU-SVE' => '66',
		'RU-SMO' => '67',
		'RU-TAM' => '68',
		'RU-TVE' => '69',
		'RU-TOM' => '70',
		'RU-TUL' => '71',
		'RU-TYU' => '72',
		'RU-ULY' => '73',
		'RU-CHE' => '74',
		'RU-YAR' => '76',
		'RU-MOW' => '77',
		'RU-SPE' => '78',
		'RU-SEV' => '92',
		'RU-YEV' => '79',
		'RU-NEN' => '83',
		'RU-KHM' => '86',
		'RU-CHU' => '87',
		'RU-YAN' => '89',
	);
}