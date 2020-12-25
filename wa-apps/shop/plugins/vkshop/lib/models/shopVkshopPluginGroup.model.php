<?php
/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 3/20/19
 * Time: 2:51 PM
 * готово к 3.3.0
 */

class shopVkshopPluginGroupModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_vkshop_group';


    /**
     * @return array
     * @throws waException
     */
    public function getLoginedGroups() {
        $groups = $this->select('*')->where('user_id IS NOT NULL')->fetchAll('id');
        foreach ($groups as $i => $group) {

            $vk = new shopVkshopPluginApi($group['app_id'], $group['app_secret'], $group['access_token'], $group['secret']);
            $vk->setApiVersion('5.87');
            if ($vk->isAuth()) {
                $groups[$i]['vk'] = $vk;
                $groups[$i]['auth'] = 1;
            }
            else {
                if (waSystemConfig::isDebug()) {
                    waLog::log('VK group ' . $group['id'] . ' auth error', 'vkshop-auth-errors.log');
                }
                $groups[$i]['auth'] = 0;
            }
        }

        return $groups;
    }

}