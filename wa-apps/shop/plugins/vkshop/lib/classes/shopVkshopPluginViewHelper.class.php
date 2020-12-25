<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/31/16
 * Time: 10:54 AM
 */
class shopVkshopPluginViewHelper
{
    /**
     * @var waView $view
     */
    private static $view;

    /**
     * @return waSmarty3View|waView
     * @throws waException
     */
    private static function getView()
    {
        if (!isset(self::$view)) {
            self::$view = waSystem::getInstance()->getView();
        }
        return self::$view;
    }

    /**
     * @var shopVkshopPlugin $plugin
     */
    private static $plugin;

    /**
     * @return shopVkshopPlugin|waPlugin
     * @throws waException
     */
    private static function getPlugin()
    {
        if (!isset(self::$plugin)) {
            self::$plugin = wa()->getPlugin('vkshop');
        }
        return self::$plugin;
    }

    /**
     * @return array
     */
    public static function getVkCats()
    {
        $vk_cats = array(
            _wp("Fashion") => array(
                1 => _wp("Women`s Clothing"),
                2 => _wp("Men`s Clothing"),
                3 => _wp("Children`s Clothing"),
                4 => _wp("Shoes &amp; Bags"),
                5 => _wp("Accessories"),
            ),
            _wp("Kids &amp; Baby") => array(
                100 => _wp("Car Safety Seats"),
                101 => _wp("Baby Carriages"),
                102 => _wp("Kids Room"),
                103 => _wp("Toys"),
                104 => _wp("Moms &amp; Babies"),
                105 => _wp("Education &amp; Art"),
                106 => _wp("School"),
            ),
            _wp("Electronics") => array(
                200 => _wp("Phones &amp; Accessories"),
                201 => _wp("Cameras"),
                202 => _wp("Audio &amp; Video"),
                203 => _wp("Portable Devices"),
                204 => _wp("Consoles &amp; Games"),
                205 => _wp("Сar Electronics"),
                206 => _wp("Scopes"),
            ),
            _wp("Computers") => array(
                300 => _wp("PC"),
                301 => _wp("Laptops &amp; Netbooks"),
                302 => _wp("Parts &amp; Accessories"),
                303 => _wp("Peripherals"),
                304 => _wp("Networking"),
                305 => _wp("Office Supplies &amp; Consumables"),
                306 => _wp("Movies, Music, Software"),
            ),
            _wp("Vehicles") => array(
                400 => _wp("Cars"),
                401 => _wp("Moto &amp; Equipment"),
                402 => _wp("Trucks &amp; Special Vehicles"),
                403 => _wp("Water Transport"),
                404 => _wp("Parts &amp; Accessories"),
            ),
            _wp("Real Estate") => array(
                500 => _wp("Apartments"),
                501 => _wp("Rooms"),
                502 => _wp("Houses, Villas, Cottages"),
                503 => _wp("Land"),
                504 => _wp("Garages &amp; Car Places"),
                505 => _wp("Commercial Property"),
                506 => _wp("International Real Estate"),
            ),
            _wp("Home") => array(
                600 => _wp("Appliances"),
                601 => _wp("Furniture &amp; Decor"),
                602 => _wp("Kitchen &amp; Dining"),
                603 => _wp("Textile"),
                604 => _wp("Household Goods"),
                605 => _wp("Building &amp; Repair"),
                606 => _wp("Country House &amp; Garden"),
            ),
            _wp("Beauty &amp; Health") => array(
                700 => _wp("Makeup"),
                701 => _wp("Fragrances"),
                702 => _wp("Skin Care"),
                703 => _wp("Tools &amp; Accessories"),
                704 => _wp("Glasses"),
            ),
            _wp("Sport &amp; Leisure") => array(
                800 => _wp("Outdoors"),
                801 => _wp("Tourism"),
                802 => _wp("Hunting &amp; Fishing"),
                803 => _wp("Gym &amp; Fitness Equipment"),
                804 => _wp("Games"),
            ),
            _wp("Spare Time &amp; Gifts") => array(
                900 => _wp("Tickets &amp; Tours"),
                901 => _wp("Books &amp; Magazines"),
                902 => _wp("Collectibles"),
                903 => _wp("Musical Instruments"),
                904 => _wp("Table Games"),
                905 => _wp("Gift Sets &amp; Certificates"),
                906 => _wp("Gifts &amp; Flowers"),
                907 => _wp("Crafts"),
            ),
            _wp("Pets") => array(
                1000 => _wp("Dogs"),
                1001 => _wp("Cats"),
                1002 => _wp("Rodents"),
                1003 => _wp("Birds"),
                1004 => _wp("Fish"),
                1005 => _wp("Other Pets"),
                1006 => _wp("Feeding &amp; Accessories"),
            ),
            _wp("Food") => array(
                1100 => _wp("Grocery"),
                1101 => _wp("Organic"),
                1102 => _wp("Baby Food"),
                1103 => _wp("Food to Order"),
                1104 => _wp("Drinks"),
            ),
            _wp("Services") => array(
                1200 => _wp("Photo &amp; Video"),
                1201 => _wp("Freelancers"),
                1202 => _wp("Events"),
                1203 => _wp("Beauty &amp; Health"),
                1204 => _wp("Equipment Service"),
                1205 => _wp("Home Improvement"),
                1206 => _wp("Delivery &amp; Transportation"),
                1207 => _wp("Education"),
                1208 => _wp("Financial services"),
                1209 => _wp("Consulting"),
            ),
        );
        return $vk_cats;
    }

    /**
     * @param $control_name
     * @return string
     * @throws waException
     */
    public static function getVkCatsHtml($control_name) {
        $vk_cats = self::getVkCats();
        $view = self::getView();
        $plugin = self::getPlugin();
        $settings = $plugin->getSettings();
        $view->assign('settings', $settings);
        $view->assign('vk_cats', $vk_cats);
        $view->assign('control_name', $control_name);

        return $view->fetch($plugin->getPluginPath() . '/templates/controls/vk_cats_select.html');
    }
}


