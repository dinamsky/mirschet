<?php
//AUTO GENERATED UPDATE FILE

$available_files = array(
	'css',
	'img',
	'js',
	'lib',
	'locale',
	'templates',
	'css/linkcanonical.css',
	'img/linkcanonical.png',
	'js/linkcanonical.js',
	'lib/actions',
	'lib/classes',
	'lib/config',
	'lib/models',
	'lib/shopLinkcanonicalPlugin.class.php',
	'lib/updates',
	'locale/ru_RU',
	'templates/Linkcanonical.html',
	'templates/actions',
	'lib/actions/shopLinkcanonicalPluginSettings.actions.php',
	'lib/classes/actions',
	'lib/classes/item',
	'lib/classes/routing',
	'lib/classes/shopLinkcanonicalHelper.class.php',
	'lib/classes/shopLinkcanonicalPagination.class.php',
	'lib/classes/shopLinkcanonicalSettings.class.php',
	'lib/classes/shopLinkcanonicalViewHelper.class.php',
	'lib/classes/url',
	'lib/classes/wa',
	'lib/config/db.php',
	'lib/config/install.php',
	'lib/config/plugin.php',
	'lib/models/shopLinkcanonicalCategoryCanonical.model.php',
	'lib/models/shopLinkcanonicalProductCanonical.model.php',
	'lib/updates/1.2',
	'lib/updates/1.3',
	'lib/updates/1.4',
	'lib/updates/1.7',
	'locale/ru_RU/LC_MESSAGES',
	'templates/actions/settings',
	'lib/classes/actions/shopLinkcanonicalCanonicalCategory.class.php',
	'lib/classes/actions/shopLinkcanonicalCanonicalProduct.class.php',
	'lib/classes/actions/shopLinkcanonicalCanonicalSettings.class.php',
	'lib/classes/actions/shopLinkcanonicalILinkCanonical.class.php',
	'lib/classes/actions/shopLinkcanonicalLinkAction.class.php',
	'lib/classes/item/shopLinkcanonicalCategoryCanonical.class.php',
	'lib/classes/item/shopLinkcanonicalIItemCanonical.class.php',
	'lib/classes/item/shopLinkcanonicalItemCanonicalList.class.php',
	'lib/classes/item/shopLinkcanonicalProductCanonical.class.php',
	'lib/classes/routing/shopLinkcanonicalRouting.class.php',
	'lib/classes/routing/shopLinkcanonicalShopRouting.class.php',
	'lib/classes/url/shopLinkcanonicalCanonicalUrl.class.php',
	'lib/classes/url/shopLinkcanonicalShopUrl.class.php',
	'lib/classes/url/shopLinkcanonicalUrl.class.php',
	'lib/classes/url/shopLinkcanonicalUrlType.class.php',
	'lib/classes/wa/shopLinkcanonicalWaRouting.class.php',
	'lib/updates/1.2/1492415432.php',
	'lib/updates/1.3/1493187823.php',
	'lib/updates/1.4/1493965206.php',
	'lib/updates/1.7/1504162978.php',
	'locale/ru_RU/LC_MESSAGES/shop_linkcanonical.mo',
	'locale/ru_RU/LC_MESSAGES/shop_linkcanonical.po',
	'templates/actions/settings/Settings.html',
);

$root_path = wa('shop')->getAppPath('plugins/linkcanonical', 'shop');
$path = $root_path . '/*';

while ($files = glob($path))
{
	foreach ($files as $file)
	{
		$_file = substr($file, strlen($root_path) + 1);

		if (!in_array($_file, $available_files))
		{
			$count = 0;

			while (true)
			{
				try
				{
					if (waFiles::delete($file))
					{
						break;
					}
				}
				catch (Exception $exception)
				{
				}

				sleep(1);
				$count++;

				if ($count > 10)
				{
					throw new waException('Не удаётся удалить файл ' . $file);
				}
			}
		}
	}

	$path .= '/*';
}
